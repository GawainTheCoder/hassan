<?php
// includes/functions.php

// Define development mode
define('DEVELOPMENT_MODE', true); // Set to false in production

/**
 * Display a stylized alert message
 * 
 * @param string $message The message to display
 * @param string $type The type of alert (success, error, info)
 * @param boolean $dismissible Whether the alert can be dismissed
 * @return void
 */
function display_alert($message, $type = 'info', $dismissible = true) {
    $class = 'notice';
    if ($type === 'success') {
        $class .= ' alert-success';
    } elseif ($type === 'error') {
        $class .= ' alert-error';
    }
    
    echo '<div class="' . $class . '">';
    echo htmlspecialchars($message);
    
    if ($dismissible) {
        echo '<button class="alert-close" aria-label="Close">&times;</button>';
    }
    
    echo '</div>';
}

/**
 * Redirect to another page
 * 
 * @param string $location The URL to redirect to
 * @param int $status The HTTP status code
 * @return void
 */
function redirect($location, $status = 302) {
    header('Location: ' . $location, true, $status);
    exit;
}

/**
 * Check if user is logged in
 * 
 * @return boolean
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return boolean
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get portfolio image URL with fallback
 * 
 * @param string $image_path The image path from the database
 * @return string The image URL
 */
function get_portfolio_image($image_path) {
    $placeholder_types = [
        'design' => 'assets/images/portfolio/placeholder-design.jpg',
        'web' => 'assets/images/portfolio/placeholder-web.jpg',
        'photography' => 'assets/images/portfolio/placeholder-photo.jpg',
        'default' => 'assets/images/portfolio/placeholder-default.jpg'
    ];
    
    if (!empty($image_path) && file_exists($image_path)) {
        return $image_path;
    }
    
    // Default placeholder
    return $placeholder_types['default'];
}

/**
 * Truncate text to a specific length
 * 
 * @param string $text The text to truncate
 * @param int $length The maximum length
 * @param string $append Text to append if truncated
 * @return string The truncated text
 */
function truncate_text($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Debug helper function
 * 
 * @param mixed $data The data to output
 * @param boolean $die Whether to stop execution after debugging
 */
function debug($data, $die = false) {
    echo '<pre style="background-color: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 20px; overflow: auto;">';
    print_r($data);
    echo '</pre>';
    
    if ($die) {
        die('Debug terminated');
    }
}

/**
 * Create database tables if they don't exist
 * 
 * @param PDO $pdo Database connection
 */
function ensure_tables_exist($pdo) {
    try {
        // Check if portfolio_items table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'portfolio_items'")->rowCount() > 0;
        
        if ($tableExists) {
            // Check if category column exists in portfolio_items table
            $result = $pdo->query("SHOW COLUMNS FROM portfolio_items LIKE 'category'");
            
            if ($result && $result->rowCount() === 0) {
                // Add category column if it doesn't exist
                $pdo->exec("ALTER TABLE portfolio_items ADD COLUMN category VARCHAR(50) DEFAULT 'web-design'");
                error_log("Added missing 'category' column to portfolio_items table");
            }
        } else {
            // Create portfolio_items table with all necessary columns
            $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                image_path VARCHAR(255),
                category VARCHAR(50) DEFAULT 'web-design',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            error_log("Created portfolio_items table");
        }
    } catch (PDOException $e) {
        error_log("Database schema update failed: " . $e->getMessage());
    }
}

/**
 * Handle fatal PHP errors and display them nicely
 */
function register_shutdown_function_handler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean(); // Clean output buffer
        
        // Only show details in development environment
        $is_development = true; // Change to false in production
        
        if ($is_development) {
            $message = "Error: {$error['message']} in {$error['file']} on line {$error['line']}";
        } else {
            $message = "An unexpected error occurred. Please try again later.";
        }
        
        include 'header.php';
        display_alert($message, 'error');
        include 'footer.php';
    }
}

// Register shutdown function to handle fatal errors
register_shutdown_function('register_shutdown_function_handler');

// Set custom error handler for non-fatal errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // Error reporting is turned off for this error level
        return false;
    }
    
    // Handle E_NOTICE errors - prevent them from being displayed
    if ($errno === E_NOTICE) {
        return true;
    }
    
    return false; // Let PHP handle other errors
});

/**
 * Validate if a file is an acceptable image
 * 
 * @param array $file The uploaded file array ($_FILES['image'])
 * @return array [success, message]
 */
function validate_image_file($file) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        return [false, get_upload_error_message($file['error'])];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return [false, "File is too large. Maximum size is 5MB."];
    }
    
    // Get file info
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    // Define allowed image types
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/webp' => 'webp'
    ];
    
    // Check MIME type
    if (!array_key_exists($mime, $allowed_types)) {
        return [false, "Only image files are allowed (JPG, PNG, GIF, BMP, WEBP)."];
    }
    
    // Return success
    return [true, "File is valid"];
}

/**
 * Get readable category name from slug
 * 
 * @param string $category_slug The category slug
 * @return string The readable category name
 */
function get_category_name($category_slug) {
    $categories = [
        'web-design' => 'Web Design',
        'graphic-design' => 'Graphic Design',
        'photography' => 'Photography',
        'illustration' => 'Illustration'
    ];
    
    return $categories[$category_slug] ?? $category_slug;
}

// Add more helper functions as required.
?>
