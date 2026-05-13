<?php
require_once __DIR__ . '/config.php';
$db = getDb();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO inventory_requests (ingredient_name, category, quantity, unit, requested_by, branch_area, priority, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([
        trim($_POST['ingredient_name'] ?? ''), trim($_POST['category'] ?? ''), trim($_POST['quantity'] ?? ''), trim($_POST['unit'] ?? ''),
        trim($_POST['requested_by'] ?? ''), trim($_POST['branch_area'] ?? ''), trim($_POST['priority'] ?? 'Normal'), trim($_POST['remarks'] ?? '')
    ]);
    $message = 'Inventory request submitted successfully.';
}
$reports = $db->query("SELECT * FROM inventory_requests ORDER BY datetime(created_at) DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Web-Based Restaurant Inventory Request System</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Segoe UI,Arial,sans-serif;min-height:100vh;color:#f5ffe9;background:url('assets/restaurant_inventory_bg.jpg') center/cover fixed no-repeat}body:before{content:"";position:fixed;inset:0;background:rgba(6,18,10,.62);backdrop-filter:blur(2px);z-index:-1}.wrap{max-width:1180px;margin:auto;padding:32px}.hero,.card{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,.35);backdrop-filter:blur(12px);padding:26px;margin-bottom:22px}.hero h1{margin:0;font-size:36px}.hero p{margin:8px 0 0;color:#dff5d2}.grid{display:grid;grid-template-columns:1fr 1.35fr;gap:22px}label{display:block;margin-top:12px;font-weight:600}input,select,textarea,button{width:100%;padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,.3);font-size:15px}input,select,textarea{background:rgba(255,255,255,.9);color:#17301b}button{margin-top:16px;background:#91c765;color:#15300e;font-weight:800;cursor:pointer}.msg{padding:12px;border-radius:12px;background:rgba(145,199,101,.35);margin-bottom:12px}table{width:100%;border-collapse:collapse;background:rgba(255,255,255,.88);color:#19351f;border-radius:16px;overflow:hidden}th,td{padding:10px;border-bottom:1px solid #dbe8d2;text-align:left;font-size:14px}th{background:#314f29;color:white}.badge{padding:5px 8px;border-radius:999px;background:#eaf6df;font-weight:700;display:inline-block}@media(max-width:900px){.grid{grid-template-columns:1fr}.hero h1{font-size:28px}}
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <h1>Web-Based  Inventory Request System</h1>
    <p>Submit your request.</p>
  </div>
  <div class="grid">
    <div class="card">
      <h2>New Inventory Request</h2>
      <?php if($message): ?><div class="msg"><?=htmlspecialchars($message)?></div><?php endif; ?>
      <form method="post">
        <label>Ingredient / Item Name</label><input name="ingredient_name" required placeholder="e.g., Fish">
        <label>Category</label><select name="category"><option>Meat</option><option>Vegetables</option><option>Seafood</option><option>Condiments</option><option>Beverages</option><option>Packaging</option><option>Cleaning Supply</option><option>Other</option></select>
        <label>Quantity</label><input name="quantity" required placeholder="e.g., 10">
        <label>Unit</label><input name="unit" required placeholder="kg, pcs, boxes, bottles">
        <label>Requested By</label><input name="requested_by" required placeholder="Staff name or station">
        <label>Branch / Area</label><input name="branch_area" required placeholder=" Kitchen, Bar, Storage Room">
        <label>Priority</label><select name="priority"><option>Normal</option><option>High</option><option>Urgent</option></select>
        <label>Remarks</label><textarea name="remarks" rows="3" placeholder="additional details"></textarea>
        <button type="submit">Submit Request</button>
      </form>
    </div>
    <div class="card">
      <h2>Recent Inventory Requests</h2>
      <table>
        <tr><th>ID</th><th>Item</th><th>Qty</th><th>Area</th><th>Priority</th><th>Status</th></tr>
        <?php foreach($reports as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['id'])?></td><td><?=htmlspecialchars($r['ingredient_name'])?></td><td><?=htmlspecialchars($r['quantity'].' '.$r['unit'])?></td><td><?=htmlspecialchars($r['branch_area'])?></td><td><?=htmlspecialchars($r['priority'])?></td><td><span class="badge"><?=htmlspecialchars($r['status'])?></span></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
</body>
</html>
