<?php
// admin/manage_courses.php
require_once '../config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

$title = $description = $skill_id = "";
$title_err = $skill_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["course_title"]))){
        $title_err = "Please enter a title.";
    } else { $title = trim($_POST["course_title"]); }

    if(empty(trim($_POST["skill_id"]))){
        $skill_err = "Please select a skill.";
    } else { $skill_id = trim($_POST["skill_id"]); }

    $description = trim($_POST["description"]);

    if(empty($title_err) && empty($skill_err)){
        $sql = "INSERT INTO courses (course_title, description, skill_id) VALUES (?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssi", $title, $description, $skill_id);
            if(mysqli_stmt_execute($stmt)){
                header("location: manage_courses.php");
                exit;
            } else { echo "Error adding course."; }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo"><span>🛡️</span> Admin Panel</div>
                <div class="nav-links">
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="manage_users.php">Users</a>
                    <a href="add_admin.php">Admins</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <h1>Manage Courses</h1>
        
        <div class="card mt-4 mb-4">
            <h3>Add New Course</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Course Title</label>
                    <input type="text" name="course_title" value="<?php echo $title; ?>">
                    <span class="text-danger"><?php echo $title_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Skill Category</label>
                    <select name="skill_id">
                        <option value="">Select Skill</option>
                        <?php
                        $skills = mysqli_query($conn, "SELECT id, skill_name FROM skills");
                        while($s = mysqli_fetch_assoc($skills)){
                            echo "<option value='".$s['id']."'>".$s['skill_name']."</option>";
                        }
                        ?>
                    </select>
                    <span class="text-danger"><?php echo $skill_err; ?></span>
                </div>
                <input type="submit" class="btn btn-primary" value="Add Course">
            </form>
        </div>

        <div class="card table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Skill Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT c.id, c.course_title, s.skill_name FROM courses c JOIN skills s ON c.skill_id = s.id ORDER BY c.id DESC";
                    $result = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['skill_name']) . "</td>";
                        echo "<td>
                                <a href='manage_course_content.php?course_id=".$row['id']."' class='btn btn-primary' style='padding:0.25rem 0.5rem; font-size:0.8rem; margin-right:5px;'>Content</a>
                                <a href='add_quiz.php?course_id=".$row['id']."' class='btn btn-secondary' style='padding:0.25rem 0.5rem; font-size:0.8rem;'>Quiz</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
