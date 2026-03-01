<?php
// admin/manage_course_content.php
require_once '../config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if($course_id == 0){
    header("location: manage_courses.php");
    exit;
}

// Get course info
$sql_course = "SELECT id, course_title FROM courses WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql_course);
mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $c_id, $c_title);
if(!mysqli_stmt_fetch($stmt)){
    header("location: manage_courses.php");
    exit;
}
mysqli_stmt_close($stmt);

// Handle module deletion
if(isset($_GET['delete_module']) && isset($_GET['module_id'])){
    $module_id = intval($_GET['module_id']);
    $sql_del = "DELETE FROM course_modules WHERE id = ? AND course_id = ?";
    $stmt = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt, "ii", $module_id, $course_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("location: manage_course_content.php?course_id=" . $course_id);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course Content - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .module-item {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .lesson-item {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
            margin: 10px 0 10px 30px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            margin: 0 2px;
        }
        .modules-list {
            margin-top: 30px;
        }
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
        <div class="mb-4">
            <a href="manage_courses.php" class="btn btn-secondary">← Back to Courses</a>
            <h1>Manage Content: <?php echo htmlspecialchars($c_title ?? 'Unknown Course'); ?></h1>
        </div>

        <!-- Add Module Form -->
        <div class="card mb-4">
            <h3>Add New Module</h3>
            <form action="add_course_module.php" method="post">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <div class="form-group">
                    <label>Module Title</label>
                    <input type="text" name="module_title" placeholder="e.g., Introduction to Web Design" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief description of this module" rows="2"></textarea>
                </div>
                <input type="submit" value="Add Module" class="btn btn-primary">
            </form>
        </div>

        <!-- Modules and Lessons List -->
        <div class="modules-list">
            <h2>Course Modules</h2>
            <?php
            $sql_modules = "SELECT id, module_title, description, module_order FROM course_modules WHERE course_id = ? ORDER BY module_order";
            $stmt = mysqli_prepare($conn, $sql_modules);
            mysqli_stmt_bind_param($stmt, "i", $course_id);
            mysqli_stmt_execute($stmt);
            $result_modules = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result_modules) == 0){
                echo '<p class="text-muted">No modules added yet. Create one using the form above.</p>';
            } else {
                while($module = mysqli_fetch_assoc($result_modules)){
                    echo '<div class="module-item">';
                    echo '<div>';
                    echo '<h4>' . htmlspecialchars($module['module_title']) . '</h4>';
                    if($module['description']) {
                        echo '<p class="text-muted">' . htmlspecialchars($module['description']) . '</p>';
                    }
                    echo '</div>';
                    echo '<div>';
                    echo '<a href="add_course_lesson.php?module_id=' . $module['id'] . '&course_id=' . $course_id . '" class="btn btn-primary btn-sm">Add Lesson</a>';
                    echo '<a href="manage_course_content.php?course_id=' . $course_id . '&delete_module=' . $module['id'] . '&module_id=' . $module['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this module and all lessons?\')">Delete</a>';
                    echo '</div>';
                    echo '</div>';

                    // Show lessons for this module
                    $sql_lessons = "SELECT id, lesson_title, lesson_order FROM course_lessons WHERE module_id = ? ORDER BY lesson_order";
                    $stmt_lessons = mysqli_prepare($conn, $sql_lessons);
                    mysqli_stmt_bind_param($stmt_lessons, "i", $module['id']);
                    mysqli_stmt_execute($stmt_lessons);
                    $result_lessons = mysqli_stmt_get_result($stmt_lessons);

                    if(mysqli_num_rows($result_lessons) > 0){
                        echo '<div>';
                        while($lesson = mysqli_fetch_assoc($result_lessons)){
                            echo '<div class="lesson-item">';
                            echo '<div>';
                            echo '<strong>' . htmlspecialchars($lesson['lesson_title']) . '</strong>';
                            echo '</div>';
                            echo '<div>';
                            echo '<a href="edit_course_lesson.php?lesson_id=' . $lesson['id'] . '&course_id=' . $course_id . '" class="btn btn-primary btn-sm">Edit</a>';
                            echo '<a href="add_course_lesson.php?module_id=' . $module['id'] . '&delete=' . $lesson['id'] . '&course_id=' . $course_id . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this lesson?\')">Delete</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    mysqli_stmt_close($stmt_lessons);
                }
            }
            mysqli_stmt_close($stmt);
            ?>
        </div>
    </div>
</body>
</html>
