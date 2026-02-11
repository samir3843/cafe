<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
require_once '../db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'list') {
        // Fetch all menu items with category names
        $stmt = $pdo->query("SELECT m.*, c.name as category_name FROM menu m LEFT JOIN categories c ON m.category_id = c.id WHERE m.status = 'active'");
        $menu = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $menu]);
    } elseif ($action === 'categories') {
        $stmt = $pdo->query("SELECT * FROM categories");
        $categories = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $categories]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $categoryId = $_POST['category_id'] ?? '';
        $price = $_POST['price'] ?? '';
        $imagePath = 'images/default_food.png';

        // File Upload Logic
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imagePath = 'backend/uploads/' . $newFileName; // Relative path for frontend
                }
            }
        } else {
             // If image_url is passed as text (fallback or update later if needed)
             $imagePath = $_POST['image_url'] ?? 'images/default_food.png';
        }

        $stmt = $pdo->prepare("INSERT INTO menu (name, category_id, price, image_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $categoryId, $price, $imagePath])) {
            echo json_encode(['success' => true, 'message' => 'Item added successfully']);
        } else {
             echo json_encode(['success' => false, 'message' => 'Failed to add item']);
        }
    } elseif ($action === 'update') {
         $id = $data['id'];
         $name = $data['name'];
         $price = $data['price'];
         // ... other fields
         $stmt = $pdo->prepare("UPDATE menu SET name=?, price=? WHERE id=?");
         if ($stmt->execute([$name, $price, $id])) {
             echo json_encode(['success' => true, 'message' => 'Item updated']);
         }
    } elseif ($action === 'delete') {
        $id = $data['id'];
        $stmt = $pdo->prepare("UPDATE menu SET status='inactive' WHERE id=?");
        if ($stmt->execute([$id])) {
             echo json_encode(['success' => true, 'message' => 'Item deleted (soft delete)']);
        }
    }
}
