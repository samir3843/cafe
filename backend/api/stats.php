<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
require_once '../db_connect.php';

$action = $_GET['action'] ?? '';

if ($action === 'dashboard_stats') {
    // Total Revenue Today (paid orders only)
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'");
    $todayRevenue = $stmt->fetchColumn() ?: 0;

    // Total Orders Today
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
    $todayOrders = $stmt->fetchColumn();

    // Staff Performance (Orders per staff today)
    $stmt = $pdo->query("SELECT u.username, COUNT(o.id) as order_count, SUM(o.total_amount) as revenue 
                         FROM orders o 
                         JOIN users u ON o.user_id = u.id 
                         WHERE DATE(o.created_at) = CURDATE() 
                         GROUP BY o.user_id");
    $staffPerformance = $stmt->fetchAll();

    // Monthly Revenue (grouped by month)
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total 
                         FROM orders 
                         WHERE payment_status = 'paid' 
                         GROUP BY month 
                         ORDER BY month DESC LIMIT 6");
    $monthlyRevenue = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => [
        'today_revenue' => $todayRevenue,
        'today_orders' => $todayOrders,
        'staff_performance' => $staffPerformance,
        'monthly_revenue' => $monthlyRevenue
    ]]);
} elseif ($action === 'users') {
    // List all users for management
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}
