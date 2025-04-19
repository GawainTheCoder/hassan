<?php
// debug.php - Only accessible in development mode
session_start();
include 'includes/config.php';
include 'includes/functions.php';
include 'includes/header.php';

// Check if this is a development environment
$is_development = true; // Set to false in production

if (!$is_development) {
    echo "<h2>Debug mode is disabled</h2>";
    include 'includes/footer.php';
    exit;
}

echo "<h2>Portfolio App Debug Information</h2>";

// Database Structure
echo "<h3>Database Structure</h3>";
try {
    // Check if portfolio_items table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'portfolio_items'");
    if ($stmt->rowCount() > 0) {
        echo "<p>Portfolio Items table exists</p>";
        
        // Get columns
        $stmt = $pdo->query("SHOW COLUMNS FROM portfolio_items");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for category column specifically
        $categoryExists = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'category') {
                $categoryExists = true;
                break;
            }
        }
        
        if (!$categoryExists) {
            echo "<p style='color:red;'>IMPORTANT: The 'category' column is missing!</p>";
            
            // Add category column button
            echo "<form method='post'>";
            echo "<input type='hidden' name='action' value='add_category_column'>";
            echo "<button type='submit'>Add Category Column to Database</button>";
            echo "</form>";
        } else {
            echo "<p style='color:green;'>The 'category' column exists.</p>";
        }
        
    } else {
        echo "<p style='color:red;'>Portfolio Items table does not exist!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Portfolio Items
echo "<h3>Portfolio Items</h3>";
try {
    $stmt = $pdo->query("SELECT pi.*, u.username FROM portfolio_items pi JOIN users u ON pi.user_id = u.id ORDER BY pi.created_at DESC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($items) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        foreach (array_keys($items[0]) as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        
        foreach ($items as $item) {
            echo "<tr>";
            foreach ($item as $key => $value) {
                if ($key === 'image_path') {
                    if (!empty($value)) {
                        echo "<td><img src='" . htmlspecialchars($value) . "' width='50'></td>";
                    } else {
                        echo "<td>No image</td>";
                    }
                } else {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No portfolio items found.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Process add category column action
if (isset($_POST['action']) && $_POST['action'] === 'add_category_column') {
    try {
        $pdo->exec("ALTER TABLE portfolio_items ADD COLUMN category VARCHAR(50) DEFAULT 'web-design'");
        echo "<p style='color:green;'>Successfully added category column.</p>";
        echo "<p>Refresh the page to see the changes.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Failed to add category column: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

include 'includes/footer.php';
?> 