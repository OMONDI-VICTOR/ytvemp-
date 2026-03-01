<?php
// quiz.php
require_once 'config.php';

// Check login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "learner"){
    header("location: login.php");
    exit;
}

if(!isset($_GET['course_id'])){
    header("location: dashboard.php");
    exit;
}

$course_id = $_GET['course_id'];
$course_title = "";

// Fetch Course Details
$sql = "SELECT course_title FROM courses WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $course_title);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if(empty($course_title)){
    echo "Invalid Course ID";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz: <?php echo htmlspecialchars($course_title); ?></title>
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
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <div class="card fade-in">
            <div class="card-header">
                <h2>Quiz: <?php echo htmlspecialchars($course_title); ?></h2>
                <p>Answer all questions to complete the course.</p>
            </div>
            
            <form action="submit_quiz.php" method="post">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                
                <?php
                $sql = "SELECT id, question, option_a, option_b, option_c, option_d FROM quizzes WHERE course_id = ?";
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "i", $course_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if(mysqli_num_rows($result) > 0){
                        $count = 1;
                        while($row = mysqli_fetch_array($result)){
                            echo '<div class="mb-4">';
                            echo '<h4>' . $count . '. ' . htmlspecialchars($row['question']) . '</h4>';
                            echo '<div class="quiz-options">';
                            
                            // Option A
                            echo '<div class="quiz-option"><label>';
                            echo '<input type="radio" name="answers['.$row['id'].']" value="A" required>';
                            echo '<span>' . htmlspecialchars($row['option_a']) . '</span>';
                            echo '</label></div>';
                            
                            // Option B
                            echo '<div class="quiz-option"><label>';
                            echo '<input type="radio" name="answers['.$row['id'].']" value="B">';
                            echo '<span>' . htmlspecialchars($row['option_b']) . '</span>';
                            echo '</label></div>';
                            
                            // Option C
                            echo '<div class="quiz-option"><label>';
                            echo '<input type="radio" name="answers['.$row['id'].']" value="C">';
                            echo '<span>' . htmlspecialchars($row['option_c']) . '</span>';
                            echo '</label></div>';
                            
                            // Option D
                            echo '<div class="quiz-option"><label>';
                            echo '<input type="radio" name="answers['.$row['id'].']" value="D">';
                            echo '<span>' . htmlspecialchars($row['option_d']) . '</span>';
                            echo '</label></div>';
                            
                            echo '</div></div>';
                            $count++;
                        }
                        echo '<div class="form-group"><button type="submit" class="btn btn-primary btn-block">Submit Quiz</button></div>';
                    } else {
                        echo '<p>No questions currently available for this course.</p>';
                    }
                    mysqli_stmt_close($stmt);
                }
                ?>
            </form>
        </div>
    </div>
</body>
</html>
