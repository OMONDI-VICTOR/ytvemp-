<?php
// dashboard.php
require_once 'config.php';

// Check login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "learner"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$user_interest = $_SESSION["interest"];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - YouthSkills</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <div class="mb-4">
            <h1>My Dashboard</h1>
            <p class="text-muted">Explore courses in your interest area: <span class="text-primary"><?php echo htmlspecialchars($user_interest); ?></span></p>
        </div>

        <?php
        // 1. Get Skill ID
        $skill_id = 0;
        $sql_skill = "SELECT id FROM skills WHERE skill_name = ?";
        if($stmt = mysqli_prepare($conn, $sql_skill)){
            mysqli_stmt_bind_param($stmt, "s", $user_interest);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt, $fetched_skill_id);
                if(mysqli_stmt_fetch($stmt)){
                    $skill_id = $fetched_skill_id;
                }
            }
            mysqli_stmt_close($stmt);
        }

        // 2. Fetch Courses
        if($skill_id > 0){
             $sql_courses = "SELECT id, course_title, description FROM courses WHERE skill_id = ?";
             if($stmt = mysqli_prepare($conn, $sql_courses)){
                 mysqli_stmt_bind_param($stmt, "i", $skill_id);
                 mysqli_stmt_execute($stmt);
                 $result_courses = mysqli_stmt_get_result($stmt);
                 
                 if(mysqli_num_rows($result_courses) > 0){
                     echo '<div class="dashboard-grid">';
                     $delay = 0;
                     while($row = mysqli_fetch_array($result_courses)){
                        $delay += 0.1;
                        $anim_style = "animation-delay: {$delay}s;";
                        // Check if user has already passed the quiz for this course
                        $passed = false;
                        $score = 0;
                        $sql_res = "SELECT score FROM results WHERE user_id = ? AND course_id = ?";
                        if($stmt_res = mysqli_prepare($conn, $sql_res)){
                            mysqli_stmt_bind_param($stmt_res, "ii", $user_id, $row['id']);
                            mysqli_stmt_execute($stmt_res);
                            mysqli_stmt_bind_result($stmt_res, $score);
                            if(mysqli_stmt_fetch($stmt_res)){
                                if($score >= 70) $passed = true;
                            }
                            mysqli_stmt_close($stmt_res); 
                        }

                         // Generates a consistent random image based on course ID
                         $img_url = "https://picsum.photos/seed/" . $row['id'] . "/400/250";

                         echo '<div class="card fade-in" style="'.$anim_style.'">';
                         echo '<img src="'.$img_url.'" alt="Course Thumbnail" class="card-img-top">';
                         echo '<h3 class="card-title">' . htmlspecialchars($row['course_title']) . '</h3>';
                         echo '<p class="mt-2 text-muted">' . htmlspecialchars($row['description']) . '</p>';
                         echo '<div class="mt-4">';
                         
                         if($passed){
                             echo '<span class="text-primary font-bold" style="display:block; margin-bottom:0.5rem;">✅ Completed (Score: '.$score.'%)</span>';
                             echo '<a href="certificate.php?course_id=' . $row['id'] . '" class="btn btn-primary btn-block" target="_blank">Download Certificate</a>';
                         } else {
                             echo '<a href="course_content.php?course_id=' . $row['id'] . '" class="btn btn-primary btn-block">View Content</a>';
                             if(isset($score) && $score > 0 && $score < 70) {
                                 echo '<span class="text-danger" style="display:block; margin-bottom:0.5rem; margin-top:0.5rem;">Last Score: '.$score.'% (Fail)</span>';
                                 echo '<a href="quiz.php?course_id=' . $row['id'] . '" class="btn btn-primary btn-block">Retake Quiz</a>';
                             } else {
                                echo '<a href="quiz.php?course_id=' . $row['id'] . '" class="btn btn-primary btn-block" style="margin-top:0.5rem;">Start Quiz</a>';
                             }
                         }

                         echo '</div>';
                         echo '</div>';
                     }
                     echo '</div>';
                 } else {
                     echo '<div class="alert alert-info">No courses found for your interest area yet. Please check back later.</div>';
                 }
                 mysqli_stmt_close($stmt);
             }
        } else {
             echo '<div class="alert alert-warning">Your selected interest area does not match any current skills database.</div>';
        }
        ?>
    </div>
</body>
</html>
