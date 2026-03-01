<?php
// Handle image uploads for TinyMCE editor
require_once 'config.php';

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploads_dir = __DIR__ . '/assets/uploads';
if(!is_dir($uploads_dir)){
    mkdir($uploads_dir, 0755, true);
}

// Allow only image files
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])){
    $file = $_FILES['file'];
    
    // Validate file
    if(!in_array($file['type'], $allowed_types)){
        http_response_code(400);
        error_log("upload_image.php: invalid file type: " . $file['type'] . " for file: " . ($file['name'] ?? '')); 
        echo json_encode(['error' => 'Invalid file type. Only images are allowed.']);
        exit;
    }
    
    if($file['size'] > 5 * 1024 * 1024){ // 5MB limit
        http_response_code(413);
        error_log("upload_image.php: file too large: " . ($file['name'] ?? '') . " size=" . $file['size']);
        echo json_encode(['error' => 'File is too large. Maximum size is 5MB.']);
        exit;
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $filepath = $uploads_dir . '/' . $filename;
    
    // Move uploaded file
    if(move_uploaded_file($file['tmp_name'], $filepath)){
        // Return the URL for the uploaded image
        $image_url = 'assets/uploads/' . $filename;
        // Log the upload for debugging
        error_log("upload_image.php: uploaded " . ($file['name'] ?? '') . " -> " . $filepath);
        echo json_encode(['location' => $image_url]);
        http_response_code(200);
    } else {
        error_log("upload_image.php: failed to move uploaded file: " . ($file['name'] ?? '') . " tmp=" . ($file['tmp_name'] ?? '')); 
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    http_response_code(400);
    error_log('upload_image.php: no file provided in POST');
    echo json_encode(['error' => 'No file provided']);
}
?>
