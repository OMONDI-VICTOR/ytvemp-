<?php
// admin/add_course_module.php
require_once '../config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if($course_id == 0){
    header("location: manage_courses.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $module_title = trim($_POST['module_title']);
    $description = trim($_POST['description']);
    
    if(!empty($module_title)){
        // Get the next module order number
        $sql_order = "SELECT MAX(module_order) as max_order FROM course_modules WHERE course_id = ?";
        $stmt = mysqli_prepare($conn, $sql_order);
        mysqli_stmt_bind_param($stmt, "i", $course_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $max_order);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        $module_order = ($max_order === NULL) ? 1 : $max_order + 1;
        
        // Insert module
        $sql = "INSERT INTO course_modules (course_id, module_title, description, module_order) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issi", $course_id, $module_title, $description, $module_order);
        
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_close($stmt);
            header("location: manage_course_content.php?course_id=" . $course_id);
            exit;
        } else {
            $error = "Error adding module. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Get course info
$c_title = "";
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Module - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <a href="manage_course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back</a>
        <h1>Add Module to: <?php echo htmlspecialchars($c_title); ?></h1>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mt-4">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                
                <div class="form-group">
                    <label for="module_title">Module Title *</label>
                    <input type="text" id="module_title" name="module_title" placeholder="e.g., Getting Started with HTML" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe what students will learn in this module..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Add Module</button>
                <a href="manage_course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
