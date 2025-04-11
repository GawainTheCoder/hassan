<?php
// includes/functions.php

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

// Add more helper functions as required.
?>
