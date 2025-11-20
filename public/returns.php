<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
require_admin(); // solo admin ve historial por defecto
$list = [];
$res = $conn->query("
                    SELECT r.*, 
                          i.code AS item_name, 
                          d.name AS dept_name, 
                          u.username AS handled_by_user
                    FROM returns r
                    LEFT JOIN items i ON r.item_id = i.id
                    LEFT JOIN departments d ON r.dept_id = d.id
                    LEFT JOIN users u ON r.handled_by = u.id
                    ORDER BY r.returned_at DESC
                ");

while($row = $res->fetch_assoc()) $list[] = $row;
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Devoluciones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<?php include("navbar.php"); ?>
<div class="container py-4">
  <h2>Historial de Devoluciones</h2>
  <table class="table">
    <thead><tr><th>Fecha</th><th>Item</th><th>Dept</th><th>Cantidad</th><th>Notas</th><th>Registró</th></tr></thead>
    <tbody>
    <?php foreach($list as $r): ?>
      <tr>
        <td><?= $r['returned_at'] ?></td>
        <td><?= htmlspecialchars($r['item_name']) ?></td>
        <td><?= htmlspecialchars($r['dept_name']) ?></td>
        <td><?= $r['quantity'] ?></td>
        <td><?= htmlspecialchars($r['notes']) ?></td>
        <td><?= htmlspecialchars($r['handled_by_user']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
