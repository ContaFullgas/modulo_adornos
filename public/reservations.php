<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      /* Miniatura en la tabla */
      .thumb { max-width: 80px; max-height: 60px; object-fit: cover; border-radius: 4px; }
      td.desc { max-width: 320px; white-space: normal; word-break: break-word; }
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2 class="mb-4">📋 Reservas</h2>

    <table class="table table-bordered align-middle">
        <thead>
        <tr>
            <th>Departamento</th>
            <th>Foto</th>
            <th>Adorno (Código)</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Usuario</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
        </thead>

        <tbody>
        <?php
        // Admin ve todo; usuario (antes department) puede ver solo su dept (opcional)
        if(current_user()['role'] === 'admin'){
            $sql = "
                SELECT r.*, d.name AS dept_name,
                       i.code AS item_code, i.description AS item_description, i.image AS item_image,
                       u.username as user_name
                FROM reservations r
                JOIN departments d ON d.id = r.dept_id
                JOIN items i ON i.id = r.item_id
                LEFT JOIN users u ON u.id = r.user_id
                ORDER BY r.reserved_at DESC
            ";
        } else {
            $dept_id = (int)current_user()['department_id'];
            $sql = "
                SELECT r.*, d.name AS dept_name,
                       i.code AS item_code, i.description AS item_description, i.image AS item_image,
                       u.username as user_name
                FROM reservations r
                JOIN departments d ON d.id = r.dept_id
                JOIN items i ON i.id = r.item_id
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.dept_id = $dept_id
                ORDER BY r.reserved_at DESC
            ";
        }

        $result = $conn->query($sql);
        if(!$result){
            echo '<tr><td colspan="9" class="text-danger">Error en la consulta: '.htmlspecialchars($conn->error ?? '') .'</td></tr>';
        } else {
            while ($row = $result->fetch_assoc()):
                // valores seguros / cast
                $dept_name = htmlspecialchars($row['dept_name'] ?? '');
                $item_code = htmlspecialchars($row['item_code'] ?? ($row['item_id'] ?? ''));
                $item_desc = $row['item_description'] ?? '';
                $item_image = $row['item_image'] ?? '';
                $quantity = (int)($row['quantity'] ?? 0);
                $user_name = htmlspecialchars($row['user_name'] ?? '');
                $status = htmlspecialchars($row['status'] ?? '');
                $reserved_at = htmlspecialchars($row['reserved_at'] ?? '');
                $reservation_id = (int)$row['id'];
                $item_id = (int)$row['item_id'];
                $row_dept_id = (int)$row['dept_id'];
        ?>
        <tr>
            <td><?= $dept_name ?></td>

            <td>
                <?php if(!empty($item_image) && file_exists(__DIR__ . '/uploads/' . $item_image)): ?>
                    <img src="uploads/<?= htmlspecialchars($item_image) ?>" alt="thumb" class="thumb">
                <?php else: ?>
                    <div class="text-muted small">Sin foto</div>
                <?php endif; ?>
            </td>

            <td><?= $item_code ?></td>

            <td class="desc"><?= nl2br(htmlspecialchars($item_desc)) ?></td>

            <td><?= $quantity ?></td>

            <td><?= $user_name ?></td>

            <td><?= $status ?></td>

            <td><?= $reserved_at ?></td>

            <td>
                <?php
                // ahora permitimos que 'usuario' (antes 'department') pueda devolver
                $role = current_user()['role'] ?? '';
                $canReturn = in_array($role, ['admin','usuario']);

                $status_l = strtolower(trim($row['status'] ?? ''));
                // mostrar botón solo si no está devuelto
                if (($status_l !== 'devuelto') && $canReturn): ?>
                    <form method="post" action="process_return.php" style="display:inline-block">
                      <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                      <input type="hidden" name="item_id" value="<?= $item_id ?>">
                      <input type="hidden" name="dept_id" value="<?= $row_dept_id ?>">
                      <input type="hidden" name="quantity" value="<?= $quantity ?>">
                      <button class="btn btn-sm btn-warning" onclick="return confirm('Registrar devolución?')">Devolver</button>
                    </form>
                <?php else: ?>
                    <span class="text-muted small">Devolución registrada</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php
            endwhile;
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
