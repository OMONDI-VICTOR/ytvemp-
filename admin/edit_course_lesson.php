<?php
// Function to convert YouTube URLs to embed format
function convertYoutubeToEmbed($url) {
    if (empty($url)) return $url;
    
    // Already an embed URL
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }
    
    // Extract video ID from watch URL: youtube.com/watch?v=VIDEO_ID
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    
    // Extract video ID from youtu.be: youtu.be/VIDEO_ID
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    
    // Return original URL if no match
    return $url;
}

require_once '../config.php';


if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if($lesson_id == 0 || $course_id == 0){
    header("location: manage_courses.php");
    exit;
}

// Get lesson details
$sql_lesson = "SELECT cl.id, cl.lesson_title, cl.content, cl.video_url, cl.duration_minutes, cl.external_resources, cl.module_id,
               cm.module_title 
               FROM course_lessons cl
               JOIN course_modules cm ON cl.module_id = cm.id
               WHERE cl.id = ? AND cm.course_id = ?";

$stmt = mysqli_prepare($conn, $sql_lesson);
mysqli_stmt_bind_param($stmt, "ii", $lesson_id, $course_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $l_id, $l_title, $l_content, $l_video, $l_duration, $m_external, $module_id, $m_title);

if(!mysqli_stmt_fetch($stmt)){
    header("location: manage_courses.php");
    exit;
}
mysqli_stmt_close($stmt);

$lesson_title = $l_title;
$content = $l_content;
$video_url = $l_video;
$duration = $l_duration;
$title_err = "";
// External resources stored as JSON in DB (array of {title,url,tag,desc})
$external_resources_json = $m_external ? $m_external : '[]';

// Load existing quiz questions for this lesson
$existing_quiz_json = '[]';
$sql_quiz = "SELECT id, question_type, question_text, section, question_order FROM lesson_quiz_questions WHERE lesson_id = ? ORDER BY question_order";
$stmt_quiz = mysqli_prepare($conn, $sql_quiz);
mysqli_stmt_bind_param($stmt_quiz, "i", $lesson_id);
mysqli_stmt_execute($stmt_quiz);
$result_quiz = mysqli_stmt_get_result($stmt_quiz);
$quiz_arr = [];
while($quizRow = mysqli_fetch_assoc($result_quiz)){
    $qid = $quizRow['id'];
    $q_item = array(
        'id' => $qid,
        'section' => $quizRow['section'],
        'type' => $quizRow['question_type'],
        'text' => $quizRow['question_text'],
        'order' => $quizRow['question_order'],
        'options' => []
    );
    
    if($quizRow['question_type'] === 'multiple_choice'){
        $sql_opts = "SELECT option_text, is_correct, option_order FROM lesson_quiz_options WHERE question_id = ? ORDER BY option_order";
        $stmt_opts = mysqli_prepare($conn, $sql_opts);
        mysqli_stmt_bind_param($stmt_opts, "i", $qid);
        mysqli_stmt_execute($stmt_opts);
        $result_opts = mysqli_stmt_get_result($stmt_opts);
        while($optRow = mysqli_fetch_assoc($result_opts)){
            $q_item['options'][] = array(
                'text' => $optRow['option_text'],
                'correct' => $optRow['is_correct'] ? true : false
            );
        }
        mysqli_stmt_close($stmt_opts);
    }
    $quiz_arr[] = $q_item;
}
mysqli_stmt_close($stmt_quiz);
$existing_quiz_json = json_encode($quiz_arr);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $lesson_title = trim($_POST['lesson_title']);
    $content = trim($_POST['content']);
    $video_url = trim($_POST['video_url']);
    $video_url = convertYoutubeToEmbed($video_url);
    $duration = !empty($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 0;
    $external_resources_json = trim($_POST['external_resources'] ?? '[]');
    
    if(empty($lesson_title)){
        $title_err = "Please enter a lesson title.";
    } else {
        // Validate external resources JSON
        $decoded_resources = json_decode($external_resources_json, true);
        if($decoded_resources === null && $external_resources_json !== '[]'){
            $error = "External resources must be valid JSON (array of objects).";
        } else {
            // Update lesson (include external_resources)
            $sql = "UPDATE course_lessons SET lesson_title = ?, content = ?, video_url = ?, duration_minutes = ?, external_resources = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssisi", $lesson_title, $content, $video_url, $duration, $external_resources_json, $lesson_id);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_close($stmt);
                
                // Delete old quiz questions and insert new ones
                $sql_del_quiz = "DELETE FROM lesson_quiz_questions WHERE lesson_id = ?";
                $stmt_del = mysqli_prepare($conn, $sql_del_quiz);
                mysqli_stmt_bind_param($stmt_del, "i", $lesson_id);
                mysqli_stmt_execute($stmt_del);
                mysqli_stmt_close($stmt_del);
                
                // Save quiz questions if provided
                if(!empty($_POST['quiz_questions_json'])){
                    $quiz_data = json_decode($_POST['quiz_questions_json'], true);
                    if(is_array($quiz_data)){
                        foreach($quiz_data as $q){
                            $q_text = $q['text'];
                            $q_type = $q['type'];
                            $q_section = $q['section'];
                            $q_order = $q['order'];
                            
                            $sql_q = "INSERT INTO lesson_quiz_questions (lesson_id, question_type, question_text, section, question_order) VALUES (?, ?, ?, ?, ?)";
                            $stmt_q = mysqli_prepare($conn, $sql_q);
                            mysqli_stmt_bind_param($stmt_q, "isssi", $lesson_id, $q_type, $q_text, $q_section, $q_order);
                            if(mysqli_stmt_execute($stmt_q)){
                                $question_id = mysqli_insert_id($conn);
                                
                                if($q_type === 'multiple_choice' && isset($q['options'])){
                                    foreach($q['options'] as $idx => $opt){
                                        $opt_text = $opt['text'];
                                        $is_correct = $opt['correct'] ? 1 : 0;
                                        $opt_order = $idx + 1;
                                        
                                        $sql_opt = "INSERT INTO lesson_quiz_options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?)";
                                        $stmt_opt = mysqli_prepare($conn, $sql_opt);
                                        mysqli_stmt_bind_param($stmt_opt, "isii", $question_id, $opt_text, $is_correct, $opt_order);
                                        mysqli_stmt_execute($stmt_opt);
                                        mysqli_stmt_close($stmt_opt);
                                    }
                                }
                            }
                            mysqli_stmt_close($stmt_q);
                        }
                    }
                }
                
                header("location: manage_course_content.php?course_id=" . $course_id . "&success=1");
                exit;
            } else {
                $error = "Error updating lesson. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lesson - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tox-tinymce { border: 1px solid #ddd; border-radius: 4px; }
        .tox-toolbar { background: #f5f5f5; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo"><span>🛡️</span> Admin Panel</div>
                <div class="nav-links">
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="manage_courses.php">Courses</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <a href="manage_course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back to Course</a>
        <h1>Edit Lesson</h1>
        
        <!-- Debug Panel -->
        <div id="debug-panel" style="background:#f0f0f0;border:2px solid #ff6b6b;border-radius:4px;padding:12px;margin:12px 0;font-family:monospace;font-size:12px;max-height:150px;overflow-y:auto;display:none;">
            <strong style="color:#ff6b6b;">🔧 TinyMCE Debug Panel</strong>
            <div id="debug-output" style="margin-top:8px;line-height:1.4;"></div>
            <button type="button" style="margin-top:8px;padding:4px 8px;background:#ff6b6b;color:white;border:none;border-radius:2px;cursor:pointer;" onclick="document.getElementById('debug-output').innerHTML='';this.textContent='Cleared';">Clear Log</button>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mt-4">
            <div class="mb-3">
                <p><strong>Module:</strong> <?php echo htmlspecialchars($m_title ?? ''); ?></p>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?lesson_id=" . $lesson_id . "&course_id=" . $course_id); ?>" method="post">
                
                <div class="form-group">
                    <label for="lesson_title">Lesson Title *</label>
                    <input type="text" id="lesson_title" name="lesson_title" value="<?php echo htmlspecialchars($lesson_title); ?>" placeholder="Lesson title" required>
                    <span class="text-danger"><?php echo $title_err; ?></span>
                </div>

                               <?php
                    // Prepare content for the editor - preserve HTML for TinyMCE to format properly
                    $editor_initial = '';
                    if(!empty($content)){
                        // Don't strip tags - TinyMCE needs the HTML!
                        $editor_initial = html_entity_decode($content);
                    }
                ?>
                <div class="form-group">
                    <label for="content">Lesson Content</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($editor_initial); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="video_url">Video URL (Optional)</label>
                    <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($video_url); ?>" placeholder="https://www.youtube.com/embed/...">
                    <small class="text-muted">Paste YouTube embed URL or direct video link</small>
                </div>

                <div class="form-group">
                    <label for="duration_minutes">Duration (minutes)</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="<?php echo htmlspecialchars($duration); ?>" min="0">
                </div>

                <div class="form-group">
                    <label>External Resources</label>
                    <div id="resources-list" style="margin-bottom:10px;"></div>
                    <button type="button" id="add-resource" class="btn btn-secondary">Add Resource</button>
                    <textarea id="external_resources" name="external_resources" rows="6" style="display:none;"><?php echo htmlspecialchars($external_resources_json); ?></textarea>
                    <small class="text-muted">Use the UI to add external resources (title, url, tag, description). They will be saved as JSON.</small>
                </div>

                <!-- Quiz Section A: Multiple Choice -->
                <div class="form-group" style="margin-top:30px;padding-top:20px;border-top:2px solid #ddd;">
                    <h4>📋 Section A: Multiple Choice Questions</h4>
                    <div id="mc-questions-container"></div>
                    <button type="button" id="add-mc-btn" class="btn btn-secondary" style="margin-top:10px;">+ Add Multiple Choice Question</button>
                </div>

                <!-- Quiz Section B: Short Answer -->
                <div class="form-group" style="margin-top:20px;">
                    <h4>📝 Section B: Short Answer Questions</h4>
                    <div id="sa-questions-container"></div>
                    <button type="button" id="add-sa-btn" class="btn btn-secondary" style="margin-top:10px;">+ Add Short Answer Question</button>
                </div>

                <input type="hidden" id="quiz_questions_json" name="quiz_questions_json" value="[]">

                <button type="submit" class="btn btn-primary">Update Lesson</button>
                <button type="button" id="preview-btn" class="btn btn-secondary">Preview</button>
                <a href="manage_course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>

<script>
// Dynamic resources UI
(function(){
    var resources = <?php echo json_encode( is_array(json_decode($external_resources_json, true)) ? json_decode($external_resources_json, true) : [] ); ?>;

    function createResourceRow(r, idx){
        var container = document.createElement('div');
        container.className = 'resource-row';
        container.style.border = '1px solid #eee';
        container.style.padding = '10px';
        container.style.marginBottom = '8px';
        container.dataset.index = idx;

        container.innerHTML =
            '<div style="display:flex;gap:8px;flex-wrap:wrap;">'+
                '<input type="text" class="res-title" placeholder="Title" style="flex:1;min-width:200px;" value="'+(r.title?escapeHtml(r.title):'')+'">'+
                '<input type="url" class="res-url" placeholder="https://..." style="flex:1;min-width:200px;" value="'+(r.url?escapeHtml(r.url):'')+'">'+
            '</div>'+
            '<div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap;">'+
                '<input type="text" class="res-tag" placeholder="Tag (topic)" style="width:200px;" value="'+(r.tag?escapeHtml(r.tag):'')+'">'+
                '<input type="text" class="res-desc" placeholder="Short description" style="flex:1;min-width:200px;" value="'+(r.desc?escapeHtml(r.desc):'')+'">'+
                '<button type="button" class="btn btn-secondary remove-resource" style="margin-left:6px;">Remove</button>'+
            '</div>';

        return container;
    }

    function escapeHtml(s){
        if(!s) return '';
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function render(){
        var list = document.getElementById('resources-list');
        list.innerHTML = '';
        resources.forEach(function(r, i){
            var row = createResourceRow(r || {}, i);
            list.appendChild(row);
        });
    }

    function gatherResources(){
        var list = document.getElementById('resources-list');
        var rows = list.querySelectorAll('.resource-row');
        var out = [];
        rows.forEach(function(row){
            var title = row.querySelector('.res-title').value.trim();
            var url = row.querySelector('.res-url').value.trim();
            var tag = row.querySelector('.res-tag').value.trim();
            var desc = row.querySelector('.res-desc').value.trim();
            if(title === '' && url === '' && tag === '' && desc === '') return; // skip empty
            out.push({title:title, url:url, tag:tag, desc:desc});
        });
        return out;
    }

    document.addEventListener('click', function(e){
        if(e.target && e.target.id === 'add-resource'){
            resources.push({title:'',url:'',tag:'',desc:''});
            render();
        }
        if(e.target && e.target.classList.contains('remove-resource')){
            var row = e.target.closest('.resource-row');
            var idx = parseInt(row.dataset.index,10);
            resources.splice(idx,1);
            render();
        }
    });

    // On form submit, encode resources into hidden textarea
    var form = document.querySelector('form');
    if(form){
        form.addEventListener('submit', function(e){
            var out = gatherResources();
            document.getElementById('external_resources').value = JSON.stringify(out);
        });
    }

    // Initial render
    render();
})();
</script>
<script>
// Quiz Management
var quizQuestions = <?php echo $existing_quiz_json; ?>;

function saveQuizData() {
    document.getElementById('quiz_questions_json').value = JSON.stringify(quizQuestions);
}

function addMCQuestion() {
    var qNum = quizQuestions.filter(q => q.section === 'A').length + 1;
    var q = {
        section: 'A',
        type: 'multiple_choice',
        text: '',
        order: quizQuestions.filter(q => q.section === 'A').length + 1,
        options: [{text: '', correct: true}, {text: '', correct: false}, {text: '', correct: false}]
    };
    quizQuestions.push(q);
    renderMCQuestions();
}

function addSAQuestion() {
    var q = {
        section: 'B',
        type: 'short_answer',
        text: '',
        order: quizQuestions.filter(q => q.section === 'B').length + 1
    };
    quizQuestions.push(q);
    renderSAQuestions();
}

function renderMCQuestions() {
    var container = document.getElementById('mc-questions-container');
    var mcQs = quizQuestions.filter(q => q.section === 'A');
    container.innerHTML = '';
    mcQs.forEach((q, idx) => {
        var qDiv = document.createElement('div');
        qDiv.style.cssText = 'background:#f9f9f9;padding:15px;margin:10px 0;border-radius:4px;border-left:4px solid #007bff;';
        qDiv.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <label><strong>Question ${idx + 1}</strong></label>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeMCQuestion(${idx})">Remove</button>
            </div>
            <textarea placeholder="Enter question" style="width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:3px;" onchange="quizQuestions[${quizQuestions.indexOf(q)}].text = this.value; saveQuizData();">${q.text}</textarea>
            <div id="options-${idx}" style="margin-left:10px;"></div>
            <button type="button" class="btn btn-secondary btn-sm" style="margin-top:10px;" onclick="addOption(${quizQuestions.indexOf(q)})">+ Add Option</button>
        `;
        container.appendChild(qDiv);
        renderOptions(quizQuestions.indexOf(q), idx);
    });
}

function renderSAQuestions() {
    var container = document.getElementById('sa-questions-container');
    var saQs = quizQuestions.filter(q => q.section === 'B');
    container.innerHTML = '';
    saQs.forEach((q, idx) => {
        var qDiv = document.createElement('div');
        qDiv.style.cssText = 'background:#f9f9f9;padding:15px;margin:10px 0;border-radius:4px;border-left:4px solid #28a745;';
        qDiv.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <label><strong>Question ${idx + 1}</strong></label>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSAQuestion(${idx})">Remove</button>
            </div>
            <textarea placeholder="Enter question" style="width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:3px;" onchange="quizQuestions[${quizQuestions.indexOf(q)}].text = this.value; saveQuizData();">${q.text}</textarea>
            <small class="text-muted">Learners will provide their own text answer</small>
        `;
        container.appendChild(qDiv);
    });
}

function renderOptions(qIdx, displayIdx) {
    var optContainer = document.getElementById('options-' + displayIdx);
    var q = quizQuestions[qIdx];
    if(!q.options) q.options = [];
    optContainer.innerHTML = '';
    q.options.forEach((opt, oIdx) => {
        var optDiv = document.createElement('div');
        optDiv.style.cssText = 'display:flex;gap:10px;margin:8px 0;';
        optDiv.innerHTML = `
            <input type="text" placeholder="Option ${String.fromCharCode(65 + oIdx)}" value="${opt.text}" style="flex:1;padding:6px;border:1px solid #ccc;border-radius:3px;" onchange="quizQuestions[${qIdx}].options[${oIdx}].text = this.value; saveQuizData();">
            <label style="display:flex;align-items:center;gap:5px;">
                <input type="checkbox" ${opt.correct ? 'checked' : ''} onchange="quizQuestions[${qIdx}].options[${oIdx}].correct = this.checked; saveQuizData();">
                Correct
            </label>
            <button type="button" class="btn btn-danger btn-sm" onclick="quizQuestions[${qIdx}].options.splice(${oIdx}, 1); renderOptions(${qIdx}, ${displayIdx}); saveQuizData();">Delete</button>
        `;
        optContainer.appendChild(optDiv);
    });
}

function removeMCQuestion(idx) {
    var mcQs = quizQuestions.filter(q => q.section === 'A');
    var actualIdx = quizQuestions.indexOf(mcQs[idx]);
    quizQuestions.splice(actualIdx, 1);
    renderMCQuestions();
    saveQuizData();
}

function removeSAQuestion(idx) {
    var saQs = quizQuestions.filter(q => q.section === 'B');
    var actualIdx = quizQuestions.indexOf(saQs[idx]);
    quizQuestions.splice(actualIdx, 1);
    renderSAQuestions();
    saveQuizData();
}

function addOption(qIdx) {
    quizQuestions[qIdx].options.push({text: '', correct: false});
    var mcQs = quizQuestions.filter(q => q.section === 'A');
    var displayIdx = mcQs.indexOf(quizQuestions[qIdx]);
    renderOptions(qIdx, displayIdx);
    saveQuizData();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-mc-btn').addEventListener('click', function(e){ e.preventDefault(); addMCQuestion(); });
    document.getElementById('add-sa-btn').addEventListener('click', function(e){ e.preventDefault(); addSAQuestion(); });
    renderMCQuestions();
    renderSAQuestions();
    document.querySelector('form').addEventListener('submit', function() {
        saveQuizData();
    });
});
</script>
<script>
// Preview action: open preview_lesson.php in a new tab with current content
document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'preview-btn'){
        if(window.tinymce){ tinymce.triggerSave(); }
        var title = document.getElementById('lesson_title').value || 'Preview';
        var content = document.getElementById('content').value || '';
        var f = document.createElement('form');
        f.method = 'POST'; f.action = 'preview_lesson.php'; f.target = '_blank';
        var t = document.createElement('input'); t.type='hidden'; t.name='preview_title'; t.value = title; f.appendChild(t);
        var c = document.createElement('input'); c.type='hidden'; c.name='preview_content'; c.value = content; f.appendChild(c);
        document.body.appendChild(f); f.submit(); document.body.removeChild(f);
    }
});
</script>



<script src="https://cdn.tiny.cloud/1/4b6nwvoy8jp9jofxc5n3xjivx15k9kkneoudhadtirepirjq/tinymce/6/tinymce.min.js"></script>
<script>
// Debug logger: output to console and to on-page debug panel
var debugLog = function(msg, isError){
    var line = '[' + new Date().toLocaleTimeString() + '] ' + msg;
    console.log(line);
    var panel = document.getElementById('debug-panel');
    var output = document.getElementById('debug-output');
    if(panel && output){
        panel.style.display = 'block';
        var span = document.createElement('div');
        span.style.color = isError ? '#ff0000' : '#333';
        span.textContent = line;
        output.appendChild(span);
        output.scrollTop = output.scrollHeight;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    try{
        debugLog('DOMContentLoaded fired');
        debugLog('TinyMCE object: ' + (window.tinymce ? 'loaded' : 'NOT loaded'));

        // Function to attempt init when tinymce is ready
        var initTinyMCE = function(attempts){
            attempts = attempts || 0;
            if(attempts > 20){
                debugLog('TinyMCE failed to load after 20 attempts (10 seconds)', true);
                return;
            }

            if(typeof tinymce === 'undefined'){
                debugLog('Waiting for TinyMCE to load... (attempt ' + (attempts+1) + '/20)');
                setTimeout(function(){ initTinyMCE(attempts + 1); }, 500);
                return;
            }

            debugLog('✓ TinyMCE library is ready');

            // Ensure any existing editor instance is removed before init
            if(window.tinymce && tinymce.get && tinymce.get('content')){
                try{ 
                    tinymce.get('content').remove(); 
                    debugLog('Removed existing TinyMCE instance');
                }catch(e){ 
                    debugLog('Error removing existing instance: ' + e.message, true);
                }
            }

            debugLog('Starting TinyMCE init for #content');
            var contentElem = document.getElementById('content');
            debugLog('Found #content element: ' + (contentElem ? 'YES' : 'NO'), !contentElem);

            tinymce.init({
                selector: '#content',
                height: 500,
                plugins: 'image paste link lists code fullscreen table heading',
                toolbar: 'formatselect | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image | table | code fullscreen',
                menubar: 'edit view insert format tools',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
                images_upload_handler: function(blobInfo, success, failure, progress) {
                    var xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '../upload_image.php');
                    xhr.upload.onprogress = function(e) {
                        if(progress) progress(e.loaded / e.total * 100);
                    };
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try{
                                var json = JSON.parse(xhr.responseText);
                                if (!json || typeof json.location != 'string') {
                                    return failure('Invalid JSON response');
                                }
                                success(json.location);
                            }catch(err){
                                failure('Invalid JSON');
                            }
                        } else {
                            failure('Upload error');
                        }
                    };
                    xhr.onerror = function() { failure('Image upload failed'); };
                    var formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                },
                paste_data_images: true,
                automatic_uploads: true,
                file_picker_types: 'image',
                file_picker_callback: function(callback, value, meta) {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.onchange = function() {
                        var file = this.files[0];
                        var reader = new FileReader();
                        reader.onload = function() {
                            var id = 'blobid' + (new Date()).getTime();
                            var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                            var base64 = reader.result.split(',')[1];
                            var blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);
                            callback(blobInfo.blobUri(), { title: file.name });
                        };
                        reader.readAsDataURL(file);
                    };
                    input.click();
                },
                setup: function(editor){
                    editor.on('init', function(){
                        debugLog('✓ TinyMCE editor initialized: ' + editor.id);
                    });
                }
            });

            // Check after a delay if editor initialized
            setTimeout(function(){
                var ed = window.tinymce && tinymce.get('content');
                debugLog('Editor status after 1.2s: ' + (ed ? 'ACTIVE' : 'NOT_FOUND'), !ed);
            }, 1200);
        };

        // Start the init process
        initTinyMCE();

    }catch(err){
        debugLog('TinyMCE init FAILED: ' + err.message, true);
        if(err.stack) debugLog('Stack: ' + err.stack, true);
    }
});
</script>
