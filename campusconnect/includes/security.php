<?php
// CSRF Protection
function generate_csrf_token() {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// File upload validation
function validate_file_upload($file, $allowed_types, $max_size = 2097152) { // 2MB default
    $errors = [];
    
    // Check file size
    if($file['size'] > $max_size) {
        $errors[] = "File size must be less than " . ($max_size / 1024 / 1024) . "MB";
    }
    
    // Check file type
    $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($file_type, $allowed_types)) {
        $errors[] = "Only " . implode(', ', $allowed_types) . " files are allowed";
    }
    
    return $errors;
}
?>