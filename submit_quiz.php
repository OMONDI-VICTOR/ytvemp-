<?php
// submit_quiz.php
require_once 'config.php';

// Check login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "learner"){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(!isset($_POST['course_id']) || !isset($_POST['answers'])){
        header("location: dashboard.php");
        exit;
    }

    $course_id = $_POST['course_id'];
    $answers = $_POST['answers'];
    $total_questions = count($answers);
    $correct_answers = 0;

    // Calculate Score
    foreach($answers as $question_id => $user_answer){
        $sql = "SELECT correct_option FROM quizzes WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $question_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $correct_option);
            if(mysqli_stmt_fetch($stmt)){
                if($user_answer === $correct_option){
                    $correct_answers++;
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Avoid division by zero
    $score_percentage = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100) : 0;
    
    // Save Result
    // Check if result exists, update if exists (best score?) or just replace?
    // Let's just create a new entry for now OR update the existing one if we want to track 'current status'.
    // The requirement implies we just need a result. I'll DELETE old result and INSERT new one for simplicity (keep latest), 
    // or UPDATE. Let's do DELETE then INSERT to keep history handling simple (or just INSERT if we want history).
    // The Schema has no unique constraint on (user_id, course_id). So we could have multiple.
    // Dashboard logic looked for *any* result. 
    // I'll delete previous attempts for this course to keep it clean for this simple app.
    
    $sql_del = "DELETE FROM results WHERE user_id = ? AND course_id = ?";
    if($stmt = mysqli_prepare($conn, $sql_del)){
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION["id"], $course_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $sql_insert = "INSERT INTO results (user_id, course_id, score) VALUES (?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql_insert)){
        mysqli_stmt_bind_param($stmt, "iii", $_SESSION["id"], $course_id, $score_percentage);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $pass_threshold = 70;
    $passed = ($score_percentage >= $pass_threshold);
} else {
    header("location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - YouthSkills</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <span>⚡</span> YouthSkills
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <div class="card text-center fade-in" style="max-width: 600px; margin: 2rem auto;">
            <?php if($passed): ?>
                <div style="font-size: 4rem;">🎉</div>
                <h1 class="text-primary">Congratulations!</h1>
                <h2 class="mt-2">You Passed!</h2>
                <p class="mb-4">You scored <strong><?php echo $score_percentage; ?>%</strong></p>
                <div class="mb-4">
                    <a href="certificate.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary" target="_blank">Download Certificate</a>
                </div>
            <?php else: ?>
                <div style="font-size: 4rem;">😕</div>
                <h1 class="text-danger">Keep Trying!</h1>
                <h2 class="mt-2">You Failed</h2>
                <p class="mb-4">You scored <strong><?php echo $score_percentage; ?>%</strong>. You need <?php echo $pass_threshold; ?>% to pass.</p>
                <div class="mb-4">
                    <a href="quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Retake Quiz</a>
                </div>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
