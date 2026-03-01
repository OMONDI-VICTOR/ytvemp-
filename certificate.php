<?php
// certificate.php
require_once 'config.php';

// Check login logic...
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if(!isset($_GET['course_id'])){
    die("Invalid Request");
}

$course_id = $_GET['course_id'];
$user_id = $_SESSION['id'];
$fullname = $_SESSION['fullname'];
$score = 0;
$course_title = "";

// Verify Pass
$passed = false;
$sql = "SELECT r.score, c.course_title 
        FROM results r 
        JOIN courses c ON r.course_id = c.id 
        WHERE r.user_id = ? AND r.course_id = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $score, $course_title);
    if(mysqli_stmt_fetch($stmt)){
        if($score >= 70){
            $passed = true;
        }
    }
    mysqli_stmt_close($stmt);
}

if(!$passed){
    die("You have not passed this course yet.");
}

// Generate Word Doc
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=Certificate_$course_title.doc");

?>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: 'Times New Roman', serif; text-align: center; border: 10px solid #787878; padding: 50px; }
    .container { border: 5px solid #787878; padding: 40px; }
    h1 { font-size: 50px; color: #4F46E5; margin-bottom: 10px; }
    h2 { font-size: 30px; margin-bottom: 30px; }
    p { font-size: 20px; }
    .name { font-size: 35px; font-weight: bold; text-decoration: underline; margin: 20px 0; }
    .course { font-size: 30px; font-weight: bold; margin: 20px 0; color: #10B981; }
    .footer { margin-top: 50px; font-size: 15px; color: #555; }
</style>
</head>
<body>
    <div class="container">
        <h1>Certificate of Completion</h1>
        <h2>This is to certify that</h2>
        <div class="name"><?php echo $fullname; ?></div>
        <p>has successfully completed the course on</p>
        <div class="course"><?php echo $course_title; ?></div>
        <p>with a score of <?php echo $score; ?>%</p>
        <div class="footer">
            <p>Date: <?php echo date("F j, Y"); ?></p>
            <p>Youth Skills Learning Application</p>
        </div>
    </div>
</body>
</html>
