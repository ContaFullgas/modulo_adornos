<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Departamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2 class="mb-4">🏢 Departamentos</h2>
    <?php if(current_user()['role'] === 'admin'): ?>
      <a href="add_department.php" class="btn btn-success mb-3">+ Agregar Departamento</a>
    <?php endif; ?>

    <table class="table table-bordered">
        <tr><th>Nombre</th><th>Contacto</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM departments");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($row["name"]) ?></td>
            <td><?= htmlspecialchars($row["contact"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
