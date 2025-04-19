<?php
// process.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Make sure our database has the necessary columns
ensure_tables_exist($pdo);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'web-design'; // Default category
        
        // For admins, allow assigning to other users; otherwise, use session user_id.
        if ($_SESSION['role'] === 'admin' && !empty($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
        } else {
            $user_id = $_SESSION['user_id'];
        }
        
        $image_path = '';
        
        // Determine absolute path for uploads
        $uploadDirBase = __DIR__ . '/uploads/'; // Use absolute path based on current file directory

        // Create uploads directory if it doesn't exist
        if (!file_exists($uploadDirBase)) {
            if (!mkdir($uploadDirBase, 0777, true)) {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Failed to create uploads directory. Check server permissions."
                ];
                header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
                exit;
            }
        } else {
            // Check writability and try to set permissions
            if (!is_writable($uploadDirBase)) {
                chmod($uploadDirBase, 0777);
                if (!is_writable($uploadDirBase)) {
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => "Uploads directory exists but is not writable. Permission denied."
                    ];
                    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
                    exit;
                }
            }
        }
        
        // Process file upload if present
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Validate the image file
            list($isValid, $message) = validate_image_file($_FILES['image']);
            
            if (!$isValid) {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Image upload failed: $message"
                ];
                header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
                exit;
            }
            
            // Create a unique filename
            $filename = basename($_FILES['image']['name']);
            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            $newFilename = uniqid() . '_' . time() . '.' . $fileExtension;
            $targetFile = $uploadDirBase . $newFilename;
            
            // Relative path to store in DB (relative to web root)
            $relativePath = 'uploads/' . $newFilename;

            error_log("Attempting to move uploaded file from: " . $_FILES['image']['tmp_name'] . " to absolute path: " . $targetFile);
            
            // Try to move the uploaded file using absolute path
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $relativePath; // Store the relative path in the DB
                error_log("File successfully moved to: " . $targetFile . ". Storing relative path: " . $relativePath);
            } else {
                // Enhanced error logging for move failure
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Failed to move uploaded file. Check server logs and directory permissions for path: $uploadDirBase"
                ];
                error_log("Failed to move file. Target: " . $targetFile . ". Error code: " . $_FILES['image']['error']);
                header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
                exit;
            }
        }
        
        // Try to insert with the category field
        try {
            $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_path, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $image_path, $category]);
            
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => "Portfolio item added successfully."
            ];
        } catch (PDOException $e) {
            // If it fails due to missing category field, try without the category
            if (strpos($e->getMessage(), "Unknown column 'category'") !== false) {
                error_log("Category column missing, trying to add it now");
                try {
                    // Try to add the column
                    $pdo->exec("ALTER TABLE portfolio_items ADD COLUMN category VARCHAR(50) DEFAULT 'web-design'");
                    
                    // Try the insert again
                    $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_path, category) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $description, $image_path, $category]);
                    
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "Portfolio item added successfully."
                    ];
                } catch (PDOException $e2) {
                    // If still failing, try without the category field
                    $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $description, $image_path]);
                    
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "Portfolio item added successfully (without category)."
                    ];
                    error_log("Added portfolio without category: " . $e2->getMessage());
                }
            } else {
                // For other errors, show the message
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Failed to add portfolio item: " . $e->getMessage()
                ];
                error_log("Portfolio add error: " . $e->getMessage());
            }
        }
    }
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
    exit;
    
} elseif ($action === 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item && ($_SESSION['role'] === 'admin' || $item['user_id'] == $_SESSION['user_id'])) {
        // Delete the image file if it exists
        if (!empty($item['image_path']) && file_exists($item['image_path'])) {
            unlink($item['image_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => "Portfolio item deleted successfully."
        ];
    }
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
    exit;
    
} elseif ($action === 'edit') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item || ($_SESSION['role'] !== 'admin' && $item['user_id'] != $_SESSION['user_id'])) {
            header("Location: user_portfolio.php");
            exit;
        }
        include 'includes/header.php';
        echo "<h2>Edit Portfolio Item</h2>";
        ?>
        <form action="process.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
            
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required><br>
            
            <label>Category:</label>
            <select name="category" required>
                <option value="web-design" <?php echo ($item['category'] ?? '') === 'web-design' ? 'selected' : ''; ?>>Web Design</option>
                <option value="graphic-design" <?php echo ($item['category'] ?? '') === 'graphic-design' ? 'selected' : ''; ?>>Graphic Design</option>
                <option value="photography" <?php echo ($item['category'] ?? '') === 'photography' ? 'selected' : ''; ?>>Photography</option>
                <option value="illustration" <?php echo ($item['category'] ?? '') === 'illustration' ? 'selected' : ''; ?>>Illustration</option>
            </select><br>
            
            <label>Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($item['description']); ?></textarea><br>
            
            <?php if (!empty($item['image_path'])): ?>
                <div class="current-image">
                    <p>Current Image:</p>
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" style="max-width: 200px; max-height: 200px;">
                </div>
            <?php endif; ?>
            
            <label>Replace Image (optional):</label>
            <input type="file" name="image" accept="image/*"><br>
            <p class="help-text">Only image files (JPG, PNG, GIF, etc.) are accepted.</p>
            
            <button type="submit">Update Item</button>
        </form>
        <?php
        include 'includes/footer.php';
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'web-design'; // Default category
        
        $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item || ($_SESSION['role'] !== 'admin' && $item['user_id'] != $_SESSION['user_id'])) {
            header("Location: user_portfolio.php");
            exit;
        }
        
        $image_path = $item['image_path'];
        
        // Determine absolute path for uploads
        $uploadDirBase = __DIR__ . '/uploads/'; // Use absolute path based on current file directory

        // Create uploads directory if it doesn't exist
        if (!file_exists($uploadDirBase)) {
            if (!mkdir($uploadDirBase, 0777, true)) {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Failed to create uploads directory. Check server permissions."
                ];
                header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
                exit;
            }
        } else {
            // Check writability and try to set permissions
            if (!is_writable($uploadDirBase)) {
                chmod($uploadDirBase, 0777);
                if (!is_writable($uploadDirBase)) {
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => "Uploads directory exists but is not writable. Permission denied."
                    ];
                    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
                    exit;
                }
            }
        }
        
        // Process file upload if present
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Validate the image file
            list($isValid, $message) = validate_image_file($_FILES['image']);
            
            if (!$isValid) {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Image upload failed: $message"
                ];
                header("Location: process.php?action=edit&id=$id");
                exit;
            }
            
            // Delete old image if exists - Use absolute path for unlinking
            if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])) {
                if (!unlink(__DIR__ . '/' . $item['image_path'])) {
                    error_log("Failed to delete old image at: " . __DIR__ . '/' . $item['image_path']);
                } else {
                    error_log("Successfully deleted old image: " . $item['image_path']);
                }
            }
            
            // Create a unique filename
            $filename = basename($_FILES['image']['name']);
            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            $newFilename = uniqid() . '_' . time() . '.' . $fileExtension;
            $targetFile = $uploadDirBase . $newFilename;
            
            // Relative path to store in DB (relative to web root)
            $relativePath = 'uploads/' . $newFilename;

            error_log("Attempting to move uploaded file from: " . $_FILES['image']['tmp_name'] . " to absolute path: " . $targetFile);
            
            // Try to move the uploaded file using absolute path
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $relativePath; // Store relative path
                error_log("File successfully moved to: " . $targetFile . ". Storing relative path: " . $relativePath);
            } else {
                // Enhanced error logging for move failure
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Failed to move uploaded file. Check server logs and directory permissions for path: $uploadDirBase"
                ];
                error_log("Failed to move file. Target: " . $targetFile . ". Error code: " . $_FILES['image']['error']);
                header("Location: process.php?action=edit&id=$id");
                exit;
            }
        }
        
        // Try to update with the category field
        try {
            $stmt = $pdo->prepare("UPDATE portfolio_items SET title = ?, description = ?, image_path = ?, category = ? WHERE id = ?");
            $stmt->execute([$title, $description, $image_path, $category, $id]);
            
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => "Portfolio item updated successfully."
            ];
        } catch (PDOException $e) {
            // If it fails due to missing category field, try without the category
            if (strpos($e->getMessage(), "Unknown column 'category'") !== false) {
                error_log("Category column missing, trying to add it now");
                try {
                    // Try to add the column
                    $pdo->exec("ALTER TABLE portfolio_items ADD COLUMN category VARCHAR(50) DEFAULT 'web-design'");
                    
                    // Try the update again
                    $stmt = $pdo->prepare("UPDATE portfolio_items SET title = ?, description = ?, image_path = ?, category = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $image_path, $category, $id]);
                    
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "Portfolio item updated successfully."
                    ];
                } catch (PDOException $e2) {
                    // If still failing, try without the category field
                    $stmt = $pdo->prepare("UPDATE portfolio_items SET title = ?, description = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $image_path, $id]);
                    
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "Portfolio item updated successfully (without category)."
                    ];
                    error_log("Updated portfolio without category: " . $e2->getMessage());
                }
            } else {
                // For other errors, show the message
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Failed to update portfolio item: " . $e->getMessage()
                ];
                error_log("Portfolio update error: " . $e->getMessage());
            }
        }
        
        header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
        exit;
    }
} else {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
    exit;
}

/**
 * Get human-readable error message for file upload errors
 */
function get_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload";
        default:
            return "Unknown upload error";
    }
}
?>
