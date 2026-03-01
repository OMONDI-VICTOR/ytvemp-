<?php
// course_content.php - View course modules and lessons for learners
require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "learner"){
    header("location: login.php");
    exit;
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$user_id = $_SESSION["id"];

if($course_id == 0){
    header("location: dashboard.php");
    exit;
}

// Get course info and verify user has access
$sql_course = "SELECT c.id, c.course_title, c.description, s.skill_name 
               FROM courses c 
               JOIN skills s ON c.skill_id = s.id 
               WHERE c.id = ? AND s.skill_name = ?";
$stmt = mysqli_prepare($conn, $sql_course);
mysqli_stmt_bind_param($stmt, "is", $course_id, $_SESSION["interest"]);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $c_id, $c_title, $c_description, $skill_name);

if(!mysqli_stmt_fetch($stmt)){
    header("location: dashboard.php");
    exit;
}
mysqli_stmt_close($stmt);

// Ensure variables are not null
$c_title = $c_title ?? 'Course';
$c_description = $c_description ?? '';

// Calculate course progress
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($c_title); ?> - YouthSkills</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .progress-section {
            margin: 20px 0;
        }
        .progress-bar {
            background: rgba(255,255,255,0.3);
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            background: #4caf50;
            height: 100%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        .module-section {
            margin-bottom: 30px;
        }
        .module-header {
            background: #f8f9fa;
            padding: 15px;
            border-left: 5px solid #667eea;
            margin-bottom: 15px;
            border-radius: 4px;
            cursor: pointer;
            user-select: none;
        }
        .module-header:hover {
            background: #e9ecef;
        }
        .module-header h3 {
            margin: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .toggle-icon {
            font-size: 20px;
        }
        .lessons-container {
            display: none;
            padding-left: 20px;
        }
        .lessons-container.active {
            display: block;
        }
        .lesson-card {
            background: white;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: box-shadow 0.3s;
        }
        .lesson-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .lesson-info h4 {
            margin: 0 0 5px 0;
        }
        .lesson-info .text-muted {
            margin: 0;
        }
        .lesson-completed {
            color: #4caf50;
            font-weight: bold;
        }
        .btn-lesson {
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
        .quiz-link {
            margin-top: 20px;
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
        <a href="dashboard.php" class="btn btn-secondary mb-4">← Back to Dashboard</a>

        <!-- Course Header -->
        <div class="course-header">
            <h1><?php echo htmlspecialchars($c_title); ?></h1>
            <p><?php echo htmlspecialchars($c_description); ?></p>
            
            <div class="progress-section">
                <p>Course Progress: <strong><?php echo $progress_percent; ?>%</strong> (<?php echo $completed_lessons; ?>/<?php echo $total_lessons; ?> lessons completed)</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;">
                        <?php echo ($progress_percent > 10) ? $progress_percent . '%' : ''; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules and Lessons -->
        <div>
            <h2>Course Content</h2>
            <?php
            $sql_modules = "SELECT id, module_title, description FROM course_modules WHERE course_id = ? ORDER BY module_order";
            $stmt = mysqli_prepare($conn, $sql_modules);
            mysqli_stmt_bind_param($stmt, "i", $course_id);
            mysqli_stmt_execute($stmt);
            $result_modules = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result_modules) == 0){
                echo '<p class="text-muted">This course doesn\'t have any content yet. Check back soon!</p>';
            } else {
                $module_count = 0;
                while($module = mysqli_fetch_assoc($result_modules)){
                    $module_count++;
                    $module_id = $module['id'];
                    
                    // Get lessons count and completed count for this module
                    $sql_lesson_count = "SELECT COUNT(*) as total FROM course_lessons WHERE module_id = ?";
                    $stmt_lc = mysqli_prepare($conn, $sql_lesson_count);
                    mysqli_stmt_bind_param($stmt_lc, "i", $module_id);
                    mysqli_stmt_execute($stmt_lc);
                    mysqli_stmt_bind_result($stmt_lc, $lesson_total);
                    mysqli_stmt_fetch($stmt_lc);
                    mysqli_stmt_close($stmt_lc);

                    $sql_lesson_completed = "SELECT COUNT(*) as completed FROM course_lessons cl 
                                           LEFT JOIN user_lesson_progress ulp ON cl.id = ulp.lesson_id AND ulp.user_id = ?
                                           WHERE cl.module_id = ? AND ulp.completed = 1";
                    $stmt_lcomp = mysqli_prepare($conn, $sql_lesson_completed);
                    mysqli_stmt_bind_param($stmt_lcomp, "ii", $user_id, $module_id);
                    mysqli_stmt_execute($stmt_lcomp);
                    mysqli_stmt_bind_result($stmt_lcomp, $lesson_completed);
                    mysqli_stmt_fetch($stmt_lcomp);
                    mysqli_stmt_close($stmt_lcomp);
                    
                    echo '<div class="module-section">';
                    echo '<div class="module-header" onclick="toggleModule(this)">';
                    echo '<h3>';
                    echo '<div>';
                    echo htmlspecialchars($module['module_title']);
                    if($module['description']) {
                        echo '<br><small class="text-muted">' . htmlspecialchars($module['description']) . '</small>';
                    }
                    echo '</div>';
                    echo '<span class="toggle-icon">▼</span>';
                    echo '</h3>';
                    echo '</div>';

                    echo '<div class="lessons-container active">';
                    
                    // Get lessons for this module
                    $sql_lessons = "SELECT id, lesson_title, duration_minutes FROM course_lessons WHERE module_id = ? ORDER BY lesson_order";
                    $stmt_l = mysqli_prepare($conn, $sql_lessons);
                    mysqli_stmt_bind_param($stmt_l, "i", $module_id);
                    mysqli_stmt_execute($stmt_l);
                    $result_lessons = mysqli_stmt_get_result($stmt_l);

                    if(mysqli_num_rows($result_lessons) > 0){
                        while($lesson = mysqli_fetch_assoc($result_lessons)){
                            // Check if lesson is completed by user
                            $sql_check = "SELECT completed FROM user_lesson_progress WHERE user_id = ? AND lesson_id = ?";
                            $stmt_check = mysqli_prepare($conn, $sql_check);
                            mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $lesson['id']);
                            mysqli_stmt_execute($stmt_check);
                            mysqli_stmt_bind_result($stmt_check, $is_completed);
                            $lesson_completed_bool = mysqli_stmt_fetch($stmt_check) ? $is_completed : false;
                            mysqli_stmt_close($stmt_check);

                            echo '<div class="lesson-card">';
                            echo '<div class="lesson-info">';
                            echo '<h4>';
                            if($lesson_completed_bool) echo '✅ ';
                            echo htmlspecialchars($lesson['lesson_title']);
                            echo '</h4>';
                            if($lesson['duration_minutes'] > 0){
                                echo '<p class="text-muted">' . $lesson['duration_minutes'] . ' minutes</p>';
                            }
                            if($lesson_completed_bool){
                                echo '<p class="lesson-completed">Completed</p>';
                            }
                            echo '</div>';
                            echo '<a href="view_lesson.php?lesson_id=' . $lesson['id'] . '&course_id=' . $course_id . '" class="btn btn-primary btn-lesson">View Lesson</a>';
                            echo '</div>';
                        }
                    }
                    mysqli_stmt_close($stmt_l);
                    
                    echo '</div>';
                    echo '</div>';
                }
            }
            mysqli_stmt_close($stmt);
            ?>
        </div>

        <!-- Quiz Section -->
        <div class="card quiz-link mt-5">
            <h3>Ready for Assessment?</h3>
            <p>Once you've completed the lessons, take the quiz to earn your certificate.</p>
            <a href="quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Take Quiz</a>
        </div>
    </div>

    <script>
        function toggleModule(element) {
            const container = element.nextElementSibling;
            container.classList.toggle('active');
            const icon = element.querySelector('.toggle-icon');
            icon.textContent = container.classList.contains('active') ? '▼' : '▶';
        }
    </script>
</body>
</html>
