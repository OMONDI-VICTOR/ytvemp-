<?php
// admin/manage_skills.php
require_once '../config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

// Handle Add Skill
$skill_name = $skill_err = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["skill_name"]))){
        $skill_err = "Please enter a skill name.";
    } else {
        $sql = "INSERT INTO skills (skill_name) VALUES (?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_skill);
            $param_skill = trim($_POST["skill_name"]);
            if(mysqli_stmt_execute($stmt)){
                header("location: manage_skills.php");
                exit;
            } else {
                $skill_err = "Something went wrong. Maybe duplicate skill?";
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
    <title>Manage Skills - Admin</title>
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
                    <a href="add_admin.php">Admins</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <h1>Manage Skills</h1>
        
        <div class="card mt-4 mb-4" style="max-width: 500px;">
            <h3>Add New Skill</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Skill Name</label>
                    <input type="text" name="skill_name" value="<?php echo $skill_name; ?>">
                    <span class="text-danger" style="font-size:0.875rem;"><?php echo $skill_err; ?></span>
                </div>
                <input type="submit" class="btn btn-primary" value="Add Skill">
            </form>
        </div>

        <div class="card table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Skill Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, skill_name FROM skills";
                    $result = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['skill_name']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
