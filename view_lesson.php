<?php
// view_lesson.php - Display individual lesson content
require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "learner"){
    header("location: login.php");
    exit;
}

$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$user_id = $_SESSION["id"];

if($lesson_id == 0 || $course_id == 0){
    header("location: dashboard.php");
    exit;
}

// Get lesson details and verify access
$sql_lesson = "SELECT cl.id, cl.lesson_title, cl.content, cl.video_url, cl.duration_minutes, cl.external_resources,
               cm.id as module_id, cm.module_title, c.course_title
               FROM course_lessons cl
               JOIN course_modules cm ON cl.module_id = cm.id
               JOIN courses c ON cm.course_id = c.id
               WHERE cl.id = ? AND c.id = ? AND c.skill_id = (SELECT id FROM skills WHERE skill_name = ?)";

$stmt = mysqli_prepare($conn, $sql_lesson);
mysqli_stmt_bind_param($stmt, "iis", $lesson_id, $course_id, $_SESSION["interest"]);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $l_id, $l_title, $l_content, $l_video, $l_duration, $l_external, $m_id, $m_title, $c_title);

if(!mysqli_stmt_fetch($stmt)){
    header("location: dashboard.php");
    exit;
}
mysqli_stmt_close($stmt);

// Ensure variables are strings and not null
$l_title = $l_title ?? 'Lesson';
$l_content = $l_content ?? '';
$l_video = $l_video ?? '';
$m_title = $m_title ?? '';
$c_title = $c_title ?? '';
// External resources JSON
$l_external = $l_external ?? '[]';

// Handle mark as complete
$completed = false;
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_complete'])){
    // Check if already marked as complete
    $sql_check = "SELECT id FROM user_lesson_progress WHERE user_id = ? AND lesson_id = ?";
    $stmt = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $lesson_id);
    mysqli_stmt_execute($stmt);
    $stmt_result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($stmt_result) > 0){
        // Update existing record
        $sql_update = "UPDATE user_lesson_progress SET completed = 1, completed_at = NOW() WHERE user_id = ? AND lesson_id = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ii", $user_id, $lesson_id);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    } else {
        // Insert new record
        $sql_insert = "INSERT INTO user_lesson_progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())";
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $lesson_id);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt);
    $completed = true;
}

// Check if lesson is already completed
$sql_progress = "SELECT completed FROM user_lesson_progress WHERE user_id = ? AND lesson_id = ?";
$stmt = mysqli_prepare($conn, $sql_progress);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $lesson_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $is_completed);
$lesson_completed = mysqli_stmt_fetch($stmt) ? $is_completed : false;
mysqli_stmt_close($stmt);
//comment
// Get next and previous lessons
$sql_next = "SELECT id, lesson_title FROM course_lessons WHERE module_id = ? AND lesson_order > (SELECT lesson_order FROM course_lessons WHERE id = ?) ORDER BY lesson_order LIMIT 1";
$stmt = mysqli_prepare($conn, $sql_next);
mysqli_stmt_bind_param($stmt, "ii", $m_id, $lesson_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $next_id, $next_title);
$has_next = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$sql_prev = "SELECT id, lesson_title FROM course_lessons WHERE module_id = ? AND lesson_order < (SELECT lesson_order FROM course_lessons WHERE id = ?) ORDER BY lesson_order DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $sql_prev);
mysqli_stmt_bind_param($stmt, "ii", $m_id, $lesson_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $prev_id, $prev_title);
$has_prev = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Ensure variables are strings and not null
$prev_title = $prev_title ?? '';
$next_title = $next_title ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($l_title); ?> - YouthSkills</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .lesson-container {
            display: grid;
            grid-template-columns: 1fr 250px;
            gap: 30px;
            margin-top: 20px;
        }
        .lesson-main {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .lesson-sidebar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
        }
        .video-container {
            margin: 20px 0;
            padding-bottom: 56.25%;
            position: relative;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .lesson-content {
            line-height: 1.8;
            font-size: 16px;
            color: #333;
        }
        .lesson-content h2 {
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .lesson-content p {
            margin-bottom: 15px;
        }
        .lesson-meta {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .completion-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .btn-complete {
            width: 100%;
            margin-bottom: 10px;
        }
        .completed-badge {
            background: #4caf50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .lesson-nav {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }
        .nav-button {
            flex: 1;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .nav-button.disabled {
            background: #ccc;
            color: #999;
            cursor: not-allowed;
        }
        .quiz-section {
            display: none;
        }
        .quiz-section.visible {
            display: block;
        }
        .take-quiz-btn {
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .lesson-container {
                grid-template-columns: 1fr;
            }
            .lesson-sidebar {
                order: -1;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <span>⚡</span> YouthSkills
                </div>
                <div class="nav-links">
                    <span>Welcome, <b><?php echo htmlspecialchars($_SESSION["fullname"]); ?></b></span>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <a href="dashboard.php">Dashboard</a> / 
            <a href="course_content.php?course_id=<?php echo $course_id; ?>"><?php echo htmlspecialchars($c_title); ?></a> / 
            <a href="course_content.php?course_id=<?php echo $course_id; ?>#"><?php echo htmlspecialchars($m_title); ?></a>
        </div>

        <div class="lesson-container">
            <!-- Main Content -->
            <div class="lesson-main">
                <h1><?php echo htmlspecialchars($l_title); ?></h1>
                
                <div class="lesson-meta">
                    <strong>Module:</strong> <?php echo htmlspecialchars($m_title); ?><br>
                    <strong>Course:</strong> <?php echo htmlspecialchars($c_title); ?>
                    <?php if($l_duration > 0): ?>
                        <br><strong>Duration:</strong> <?php echo $l_duration; ?> minutes
                    <?php endif; ?>
                </div>

                <?php if($lesson_completed): ?>
                    <div class="completed-badge">✅ You have completed this lesson</div>
                <?php endif; ?>

                <!-- Video Section -->
                <?php if(!empty($l_video)): ?>
                    <div class="video-container">
                        <iframe src="<?php echo htmlspecialchars($l_video); ?>" allowfullscreen="allowfullscreen"></iframe>
                    </div>
                <?php endif; ?>

                <!-- Lesson Content -->
                <div class="lesson-content">
                    <?php echo $l_content; ?>
                </div>

                <!-- External Resources -->
                <?php
                $learner_interest = $_SESSION['interest'] ?? $_GET['interest'] ?? null;
                $resources = json_decode($l_external ?? '[]', true);
                if(!is_array($resources)) $resources = [];
                if(count($resources) > 0): ?>
                    <h3 style="margin-top:20px;">Further Reading & Resources</h3>
                    <?php foreach($resources as $r):
                        $r_title = htmlspecialchars($r['title'] ?? 'Resource');
                        $r_url = $r['url'] ?? '';
                        $r_tag = htmlspecialchars($r['tag'] ?? '');
                        $r_desc = htmlspecialchars($r['desc'] ?? '');
                        if($learner_interest && $r_tag !== '' && stripos($r_tag, $learner_interest) === false) continue;
                        $valid_url = filter_var($r_url, FILTER_VALIDATE_URL) ? htmlspecialchars($r_url) : '#';
                    ?>
                        <div class="resource-card" style="border:1px solid #eee;padding:12px;border-radius:6px;margin-bottom:10px;">
                            <a href="<?php echo $valid_url; ?>" target="_blank" rel="noopener noreferrer" style="font-weight:bold;color:#2b6cb0;text-decoration:none;">
                                <?php echo $r_title; ?>
                            </a>
                            <?php if($r_tag): ?> <small style="color:#666">[<?php echo $r_tag; ?>]</small><?php endif; ?>
                            <?php if($r_desc): ?><div style="margin-top:6px;color:#444"><?php echo $r_desc; ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <a href="quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-success" style="width: 100%; text-align: center; display: block; text-decoration: none; margin-top: 10px;">Take Quiz</a>
            </div>

            <!-- Sidebar -->
            <div class="lesson-sidebar">
                <h3>Course Progress</h3>
                <?php
                // Get total and completed lessons for this course
                $sql_total = "SELECT COUNT(*) as total FROM course_lessons WHERE module_id IN (SELECT id FROM course_modules WHERE course_id = ?)";
                $stmt = mysqli_prepare($conn, $sql_total);
                mysqli_stmt_bind_param($stmt, "i", $course_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $total_lessons);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                $sql_completed = "SELECT COUNT(DISTINCT ulp.lesson_id) FROM user_lesson_progress ulp 
                                JOIN course_lessons cl ON ulp.lesson_id = cl.id 
                                JOIN course_modules cm ON cl.module_id = cm.id 
                                WHERE cm.course_id = ? AND ulp.user_id = ? AND ulp.completed = 1";
                $stmt = mysqli_prepare($conn, $sql_completed);
                mysqli_stmt_bind_param($stmt, "ii", $course_id, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $completed_lessons);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                $progress_percent = ($total_lessons > 0) ? round(($completed_lessons / $total_lessons) * 100) : 0;
                ?>
                <p><strong><?php echo $completed_lessons; ?>/<?php echo $total_lessons; ?> Lessons</strong></p>
                <div style="background: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden; margin-bottom: 15px;">
                    <div style="background: #4caf50; height: 100%; width: <?php echo $progress_percent; ?>%; transition: width 0.3s;"></div>
                </div>
                <p style="font-size: 14px; color: #666; margin-bottom: 20px;"><?php echo $progress_percent; ?>% Complete</p>

                <a href="course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary" style="width: 100%; text-align: center; display: block; margin-bottom: 10px; text-decoration: none;">Course Overview</a>
                
                <hr style="margin: 20px 0;">
                
                <h4>Ready for Quiz?</h4>
                <p style="font-size: 14px; margin-bottom: 10px;">Complete all lessons and take the course quiz to earn your certificate.</p>
                <a href="quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-success" style="width: 100%; text-align: center; display: block; text-decoration: none; margin-top: 10px;">Take Quiz</a>
            </div>
        </div>
    </div>
</body>
</html>
