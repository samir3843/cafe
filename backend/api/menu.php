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
        // Admin only - assume auth check is done in frontend or middleware (simplified here)
        $name = $data['name'];
        $categoryId = $data['category_id'];
        $price = $data['price'];
        $image = $data['image_url'] ?? 'images/default_food.png';
        
        $stmt = $pdo->prepare("INSERT INTO menu (name, category_id, price, image_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $categoryId, $price, $image])) {
            echo json_encode(['success' => true, 'message' => 'Item added']);
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
