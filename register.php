<?php
// register.php
require_once 'config.php';

$fullname = $email = $password = $interest = "";
$fullname_err = $email_err = $password_err = $interest_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Full Name
    if (empty(trim($_POST["fullname"]))) {
        $fullname_err = "Please enter your full name.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate Interest
    if (empty(trim($_POST["interest"]))) {
        $interest_err = "Please select an area of interest.";
    } else {
        $interest = trim($_POST["interest"]);
    }

    // Check input errors before inserting in database
    if (empty($fullname_err) && empty($email_err) && empty($password_err) && empty($interest_err)) {
        
        $sql = "INSERT INTO users (fullname, email, password, interest) VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_fullname, $param_email, $param_password, $param_interest);

            $param_fullname = $fullname;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $param_interest = $interest;

            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header("location: login.php");
            } else {
                echo "Something went wrong. Please try again later.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YouthSkills</title>
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
                    <a href="index.php">Home</a>
                    <a href="login.php">Login</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container auth-container">
        <div class="card fade-in">
            <div class="card-header text-center">
                <h2>Create an Account</h2>
                <p class="text-muted">Join us and start learning today</p>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="<?php echo $fullname; ?>">
                    <span class="text-danger" style="color:red; font-size:0.875rem;"><?php echo $fullname_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo $email; ?>">
                    <span class="text-danger" style="color:red; font-size:0.875rem;"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" value="<?php echo $password; ?>">
                    <span class="text-danger" style="color:red; font-size:0.875rem;"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Area of Interest</label>
                    <select name="interest">
                        <option value="">Select an Interest</option>
                        <option value="Web Development" <?php if($interest == "Web Development") echo "selected"; ?>>Web Development</option>
                        <option value="Data Science" <?php if($interest == "Data Science") echo "selected"; ?>>Data Science</option>
                        <option value="Graphic Design" <?php if($interest == "Graphic Design") echo "selected"; ?>>Graphic Design</option>
                        <option value="Digital Marketing" <?php if($interest == "Digital Marketing") echo "selected"; ?>>Digital Marketing</option>
                    </select>
                    <span class="text-danger" style="color:red; font-size:0.875rem;"><?php echo $interest_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary btn-block" value="Register">
                </div>
                <p class="text-center">Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
        </div>
    </div>
</body>
</html>
