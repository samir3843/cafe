<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
require_once '../db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'create') {
        // Create new order
        $userId = $data['user_id']; // Staff ID
        $tableNumber = $data['table_number'];
        $customerName = $data['customer_name'];
        $items = $data['items']; // Array of {menu_id, quantity, price}
        
        $orderCode = 'ORD' . date('YmdHis') . rand(100, 999);
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, table_number, customer_name, total_amount) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$orderCode, $userId, $tableNumber, $customerName, $totalAmount]);
            $orderId = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$orderId, $item['menu_id'], $item['quantity'], $item['price']]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Order created', 'order_code' => $orderCode]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
        }
    } elseif ($action === 'update_status') {
        // For Kitchen or Staff
        $orderId = $data['order_id'];
        $statusType = $data['status_type']; // 'kitchen' or 'payment'
        $statusValue = $data['status_value'];

        if ($statusType === 'kitchen') {
            $stmt = $pdo->prepare("UPDATE orders SET kitchen_status = ? WHERE id = ?");
        } elseif ($statusType === 'payment') {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        } else {
             echo json_encode(['success' => false, 'message' => 'Invalid status type']);
             exit;
        }

        if ($stmt->execute([$statusValue, $orderId])) {
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
    }
} elseif ($method === 'GET') {
    if ($action === 'list_active') {
        // For Kitchen: pending or preparing
        // Fetch items with details: name::quantity::image_url
        $stmt = $pdo->query("SELECT o.*, 
                             GROUP_CONCAT(CONCAT(m.name, '::', oi.quantity, '::', IFNULL(m.image_url, '')) SEPARATOR '||') as items_details 
                             FROM orders o 
                             JOIN order_items oi ON o.id = oi.order_id 
                             JOIN menu m ON oi.menu_id = m.id 
                             WHERE o.kitchen_status IN ('pending', 'preparing') 
                             GROUP BY o.id ORDER BY o.created_at ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'list_today') {
        // For Staff/Admin: All orders today
        $stmt = $pdo->query("SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'details') {
        $orderId = $_GET['id'];
        $stmt = $pdo->prepare("SELECT o.*, u.username as staff_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        $stmtItems = $pdo->prepare("SELECT oi.*, m.name as menu_name FROM order_items oi LEFT JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();
        
        echo json_encode(['success' => true, 'data' => ['order' => $order, 'items' => $items]]);
    }
}
