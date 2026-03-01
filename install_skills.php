<?php
// install_skills.php
// Run this file once to populate the skills table with default interests matching the registration form.

require_once 'config.php';

// List of skills to ensure exist
$skills_to_add = [
    "Web Development",
    "Data Science",
    "Graphic Design",
    "Digital Marketing"
];

echo "<h2>Initializing Skills Database...</h2>";

// Check if skills table exists, if not create it (though it should exist from previous steps)
$check_table = "CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $check_table)) {
    echo "✅ Skills table check passed.<br>";
} else {
    echo "❌ Error checking/creating skills table: " . mysqli_error($conn) . "<br>";
}

// Insert skills
foreach ($skills_to_add as $skill) {
    // Check if exists
    $check_sql = "SELECT id FROM skills WHERE skill_name = ?";
    if ($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "s", $skill);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 0) {
            // Does not exist, insert it
            $insert_sql = "INSERT INTO skills (skill_name, description) VALUES (?, ?)";
            if ($insert_stmt = mysqli_prepare($conn, $insert_sql)) {
                $desc = "Learn about " . $skill;
                mysqli_stmt_bind_param($insert_stmt, "ss", $skill, $desc);
                if (mysqli_stmt_execute($insert_stmt)) {
                    echo "✅ Added skill: <strong>$skill</strong><br>";
                } else {
                    echo "❌ Failed to add skill: $skill - " . mysqli_error($conn) . "<br>";
                }
                mysqli_stmt_close($insert_stmt);
            }
        } else {
            echo "ℹ️ Skill already exists: <strong>$skill</strong><br>";
        }
        mysqli_stmt_close($stmt);
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Log in as <strong>Admin</strong>.</li>";
echo "<li>Go to <strong>Manage Courses</strong>.</li>";
echo "<li>Create a new course and select one of the skills above as the category.</li>";
echo "<li>Add Modules and Lessons to that course.</li>";
echo "<li>Register a new User with that specific interest to test the Dashboard.</li>";
echo "</ol>";
echo "<a href='admin/admin_login.php'>Go to Admin Login</a>";
?>
