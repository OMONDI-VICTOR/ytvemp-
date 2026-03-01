<?php
// admin/add_quiz.php
require_once '../config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}

if(!isset($_GET['course_id']) && !isset($_POST['course_id'])){
    header("location: manage_courses.php");
    exit;
}

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : $_POST['course_id'];
$question = $optA = $optB = $optC = $optD = $correct = "";
$msg = "";

// Fetch Course Name
$sql_c = "SELECT course_title FROM courses WHERE id = ?";
$stmt_c = mysqli_prepare($conn, $sql_c);
mysqli_stmt_bind_param($stmt_c, "i", $course_id);
mysqli_stmt_execute($stmt_c);
mysqli_stmt_bind_result($stmt_c, $course_title);
mysqli_stmt_fetch($stmt_c);
mysqli_stmt_close($stmt_c);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $question = trim($_POST["question"]);
    $optA = trim($_POST["option_a"]);
    $optB = trim($_POST["option_b"]);
    $optC = trim($_POST["option_c"]);
    $optD = trim($_POST["option_d"]);
    $correct = trim($_POST["correct_option"]);

    if(!empty($question) && !empty($optA) && !empty($correct)){
        $sql = "INSERT INTO quizzes (course_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "issssss", $course_id, $question, $optA, $optB, $optC, $optD, $correct);
            if(mysqli_stmt_execute($stmt)){
                $msg = "Question added successfully.";
                // Clear fields
                $question = $optA = $optB = $optC = $optD = $correct = "";
            } else {
                $msg = "Error adding question.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $msg = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quiz - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo"><span>🛡️</span> Admin Panel</div>
                <div class="nav-links">
                    <a href="manage_courses.php">Back to Courses</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container wrapper mt-4">
        <h1>Quiz Questions for: <?php echo htmlspecialchars($course_title); ?></h1>
        
        <?php if(!empty($msg)) echo '<div class="alert alert-success">'.$msg.'</div>'; ?>

        <div class="card mt-4 mb-4">
            <h3>Add New Question</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <div class="form-group">
                    <label>Question</label>
                    <textarea name="question" rows="2" required><?php echo $question; ?></textarea>
                </div>
                <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0;">
                    <div class="form-group">
                        <label>Option A</label>
                        <input type="text" name="option_a" value="<?php echo $optA; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Option B</label>
                        <input type="text" name="option_b" value="<?php echo $optB; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Option C</label>
                        <input type="text" name="option_c" value="<?php echo $optC; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Option D</label>
                        <input type="text" name="option_d" value="<?php echo $optD; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Correct Option</label>
                    <select name="correct_option" required>
                        <option value="">Select Correct Answer</option>
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>
                <input type="submit" class="btn btn-primary" value="Add Question">
            </form>
        </div>

        <div class="card table-responsive">
            <h3 class="mb-2">Existing Questions</h3>
            <table>
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="40%">Question</th>
                        <th width="10%">Correct</th>
                        <!-- <th width="10%">Action</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, question, correct_option FROM quizzes WHERE course_id = ? ORDER BY id ASC";
                    if($stmt = mysqli_prepare($conn, $sql)){
                        mysqli_stmt_bind_param($stmt, "i", $course_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['question']) . "</td>";
                            echo "<td>" . $row['correct_option'] . "</td>";
                            echo "</tr>";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
