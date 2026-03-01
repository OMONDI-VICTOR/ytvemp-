<?php
require_once '../config.php';
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: admin_login.php");
    exit;
}
$preview_title = isset($_POST['preview_title']) ? $_POST['preview_title'] : 'Preview';
$preview_content = isset($_POST['preview_content']) ? $_POST['preview_content'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Preview - <?php echo htmlspecialchars($preview_title); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body{ background:#f6f8fa; }
        .preview-container{ max-width:900px; margin:30px auto; background:white; padding:24px; border-radius:6px; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
        .preview-content{ line-height:1.8; font-size:16px; color:#222; }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-container">
            <h1><?php echo htmlspecialchars($preview_title); ?></h1>
            <div class="preview-content">
                <?php echo $preview_content; ?>
            </div>
            <div style="margin-top:20px;">
                <a href="#" onclick="window.close();return false;" class="btn btn-secondary">Close Preview</a>
            </div>
        </div>
    </div>
</body>
</html>
