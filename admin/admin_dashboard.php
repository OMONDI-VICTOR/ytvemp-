<?php
// admin/admin_dashboard.php
require_once '../config.php';

// Check admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

// Fetch stats
$total_users = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) FROM users");
if($row = mysqli_fetch_array($result)) $total_users = $row[0];

$total_skills = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) FROM skills");
if($row = mysqli_fetch_array($result)) $total_skills = $row[0];

$total_courses = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) FROM courses");
if($row = mysqli_fetch_array($result)) $total_courses = $row[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YouthSkills</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <span>🛡️</span> Admin Panel
                </div>
                <div class="nav-links">
                    <a href="manage_users.php">Users</a>
                    <a href="manage_skills.php">Skills</a>
                    <a href="manage_courses.php">Courses</a>
                    <a href="add_admin.php">Admins</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <h1>Dashboard Overview</h1>
        
        <div class="dashboard-grid">
            <div class="card text-center fade-in" style="animation-delay: 0.1s;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">👥</div>
                <h2><?php echo $total_users; ?></h2>
                <p class="text-muted">Registered Users</p>
                <a href="manage_users.php" class="btn btn-primary mt-4">Manage Users</a>
            </div>
            <div class="card text-center fade-in" style="animation-delay: 0.2s;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚡</div>
                <h2><?php echo $total_skills; ?></h2>
                <p class="text-muted">Active Skills</p>
                <a href="manage_skills.php" class="btn btn-primary mt-4">Manage Skills</a>
            </div>
            <div class="card text-center fade-in" style="animation-delay: 0.3s;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">📚</div>
                <h2><?php echo $total_courses; ?></h2>
                <p class="text-muted">Total Courses</p>
                <a href="manage_courses.php" class="btn btn-primary mt-4">Manage Courses</a>
            </div>
            <div class="card text-center fade-in" style="animation-delay: 0.4s;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔑</div>
                <h2>Admins</h2>
                <p class="text-muted">System Admins</p>
                <a href="add_admin.php" class="btn btn-secondary mt-4">Manage Admins</a>
            </div>
        </div>
    </div>
</body>
</html>
