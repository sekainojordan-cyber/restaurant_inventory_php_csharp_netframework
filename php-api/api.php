<?php
require_once __DIR__ . '/config.php';
$db = getDb();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT * FROM inventory_requests ORDER BY datetime(created_at) DESC, id DESC");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'add') {
        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true) ?: [];
        }
        $required = ['ingredient_name','category','quantity','unit','requested_by','branch_area','priority'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || trim($input[$field]) === '') {
                jsonResponse(['success' => false, 'message' => "Missing field: $field"], 400);
            }
        }
        $stmt = $db->prepare("INSERT INTO inventory_requests (ingredient_name, category, quantity, unit, requested_by, branch_area, priority, remarks, status) VALUES (:ingredient_name, :category, :quantity, :unit, :requested_by, :branch_area, :priority, :remarks, 'Pending')");
        $stmt->execute([
            ':ingredient_name' => trim($input['ingredient_name']),
            ':category' => trim($input['category']),
            ':quantity' => trim($input['quantity']),
            ':unit' => trim($input['unit']),
            ':requested_by' => trim($input['requested_by']),
            ':branch_area' => trim($input['branch_area']),
            ':priority' => trim($input['priority']),
            ':remarks' => trim($input['remarks'] ?? '')
        ]);
        jsonResponse(['success' => true, 'message' => 'Inventory request submitted.', 'id' => $db->lastInsertId()]);
    }

    if ($action === 'update_status') {
        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true) ?: [];
        }
        $id = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $allowed = ['Pending','Approved','For Purchase','Stocked','Rejected','Cancelled'];
        if ($id <= 0 || !in_array($status, $allowed, true)) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID or status.'], 400);
        }
        $stmt = $db->prepare("UPDATE inventory_requests SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
        jsonResponse(['success' => true, 'message' => 'Status updated.']);
    }

    if ($action === 'summary') {
        $rows = $db->query("SELECT status, COUNT(*) AS total FROM inventory_requests GROUP BY status ORDER BY status")->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success' => true, 'data' => $rows]);
    }

    jsonResponse(['success' => false, 'message' => 'Unknown API action.'], 404);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
