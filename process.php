<?php
// process.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        // For admins, allow assigning to other users; otherwise, use session user_id.
        if ($_SESSION['role'] === 'admin' && !empty($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
        } else {
            $user_id = $_SESSION['user_id'];
        }
        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $filename = basename($_FILES['image']['name']);
            $targetFile = $uploadDir . time() . "_" . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $image_path]);
    }
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
    exit;
    
} elseif ($action === 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item && ($_SESSION['role'] === 'admin' || $item['user_id'] == $_SESSION['user_id'])) {
        $stmt = $pdo->prepare("DELETE FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
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
            <label>Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($item['description']); ?></textarea><br>
            <label>Replace Image (optional):</label>
            <input type="file" name="image"><br>
            <button type="submit">Update Item</button>
        </form>
        <?php
        include 'includes/footer.php';
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        
        $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item || ($_SESSION['role'] !== 'admin' && $item['user_id'] != $_SESSION['user_id'])) {
            header("Location: user_portfolio.php");
            exit;
        }
        
        $image_path = $item['image_path'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $filename = basename($_FILES['image']['name']);
            $targetFile = $uploadDir . time() . "_" . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            }
        }
        
        $stmt = $pdo->prepare("UPDATE portfolio_items SET title = ?, description = ?, image_path = ? WHERE id = ?");
        $stmt->execute([$title, $description, $image_path, $id]);
        header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
        exit;
    }
} else {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin.php" : "user_portfolio.php"));
    exit;
}
?>
