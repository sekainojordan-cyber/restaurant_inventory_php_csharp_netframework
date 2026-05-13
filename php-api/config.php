<?php
function getDb() {
    $dbFile = __DIR__ . DIRECTORY_SEPARATOR . 'restaurant_inventory.sqlite';
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS inventory_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ingredient_name TEXT NOT NULL,
        category TEXT NOT NULL,
        quantity TEXT NOT NULL,
        unit TEXT NOT NULL,
        requested_by TEXT NOT NULL,
        branch_area TEXT NOT NULL,
        priority TEXT NOT NULL DEFAULT 'Normal',
        remarks TEXT,
        status TEXT NOT NULL DEFAULT 'Pending',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT
    )");
    seedData($db);
    return $db;
}
function seedData($db) {
    $count = (int)$db->query("SELECT COUNT(*) FROM inventory_requests")->fetchColumn();
    if ($count > 0) return;
    $stmt = $db->prepare("INSERT INTO inventory_requests (ingredient_name, category, quantity, unit, requested_by, branch_area, priority, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $rows = [
        ['Chicken Breast', 'Meat', '15', 'kg', 'Kitchen Staff', 'Main Kitchen', 'High', 'Needed for grilled chicken menu', 'Pending'],
        ['Tomato Sauce', 'Condiments', '12', 'bottles', 'Chef Marco', 'Sauce Station', 'Normal', 'For pasta and pizza orders', 'Approved'],
        ['Lettuce', 'Vegetables', '8', 'kg', 'Salad Prep', 'Cold Station', 'Urgent', 'Low stock before dinner service', 'For Purchase'],
        ['Disposable Takeout Boxes', 'Packaging', '300', 'pcs', 'Cashier Area', 'Front Counter', 'Normal', 'For takeout orders', 'Stocked']
    ];
    foreach ($rows as $r) $stmt->execute($r);
}
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?>
