<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouthSkills - Future Ready</title>
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
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <a href="logout.php" class="btn btn-secondary">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php" class="btn btn-primary">Get Started</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="wrapper">
        <section class="hero">
            <div class="container" style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap;">
                <div class="fade-in" style="flex: 1; min-width: 300px; padding: 2rem;">
                    <h1>Level Up Your Skills 🚀</h1>
                    <p>Join thousands of youth learners mastering the future. Interactive courses, real-world skills, and instant certificates.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="register.php" class="btn btn-secondary">Start Learning Now</a>
                        <a href="login.php" class="btn" style="color: white; border: 1px solid rgba(255,255,255,0.3);">Login</a>
                    </div>
                </div>
                <!-- 3D Illustration Placeholder -->
                <div class="fade-in" style="flex: 1; min-width: 300px; text-align: center; animation-delay: 0.2s;">
                     <img src="https://cdn3d.iconscout.com/3d/premium/thumb/rocket-3214878-2691586.png?f=webp" alt="Lift off" style="max-width: 100%; height: auto; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));">
                </div>
            </div>
        </section>

        <section class="container mt-4 mb-4">
            <div class="dashboard-grid">
                <div class="card fade-in" style="animation-delay: 0.1s;">
                    <h3 class="card-title">📚 Interactive Learning</h3>
                    <p class="text-muted">Engaging content designed for the modern youth mind.</p>
                </div>
                <div class="card fade-in" style="animation-delay: 0.2s;">
                    <h3 class="card-title">🏆 Earn Certificates</h3>
                    <p class="text-muted">Prove your skills with verifiable certificates upon completion.</p>
                </div>
                <div class="card fade-in" style="animation-delay: 0.3s;">
                    <h3 class="card-title">🌍 Future Ready</h3>
                    <p class="text-muted">Skills that matter in the real world marketplace.</p>
                </div>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> YouthSkills. Empowering the next generation.</p>
        </div>
    </footer>
</body>
</html>
