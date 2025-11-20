<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2 class="mb-4">📋 Reservas</h2>

    <table class="table table-bordered">
        <tr>
            <th>Departamento</th>
            <th>Adorno</th>
            <th>Cantidad</th>
            <th>Usuario</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>

        <?php
        // Admin ve todo; departamento puede ver solo su dept (opcional)
        if(current_user()['role'] === 'admin'){
            $sql = "
                SELECT r.*, d.name AS dept_name, i.code AS item_name, u.username as user_name
                FROM reservations r
                JOIN departments d ON d.id = r.dept_id
                JOIN items i ON i.id = r.item_id
                LEFT JOIN users u ON u.id = r.user_id
                ORDER BY r.reserved_at DESC
            ";

        } else {
            $dept_id = (int)current_user()['department_id'];
            $sql = "
                SELECT r.*, d.name AS dept_name, i.code AS item_name, u.username as user_name
                FROM reservations r
                JOIN departments d ON d.id = r.dept_id
                JOIN items i ON i.id = r.item_id
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.dept_id = $dept_id
                ORDER BY r.reserved_at DESC
            ";

        }
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($row['dept_name']) ?></td>
            <td><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= $row['status'] ?></td>
            <td><?= $row['reserved_at'] ?></td>
            <td>
                <?php
                $status = strtolower(trim($row['status'] ?? ''));
                // mostrar botón solo si no está devuelto
                if (($status !== 'returned') && (current_user()['role'] === 'admin' || current_user()['role'] === 'department')): ?>
                    <form method="post" action="process_return.php" style="display:inline-block">
                    <input type="hidden" name="reservation_id" value="<?= (int)$row['id'] ?>">
                    <input type="hidden" name="item_id" value="<?= (int)$row['item_id'] ?>">
                    <input type="hidden" name="dept_id" value="<?= (int)$row['dept_id'] ?>">
                    <input type="hidden" name="quantity" value="<?= (int)$row['quantity'] ?>">
                    <button class="btn btn-sm btn-warning" onclick="return confirm('Registrar devolución?')">Devolver</button>
                    </form>
                <?php else: ?>
                    <span class="text-muted">Devolución registrada</span>
                <?php endif; ?>
            </td>

        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
