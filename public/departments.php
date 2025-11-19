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
      <!-- BOTÓN que abre el modal (no es un enlace) -->
      <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addDeptModal">
        + Agregar Departamento
      </button>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead><tr><th>Nombre</th></tr></thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT * FROM departments ORDER BY name");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($row["name"]) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar departamento -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="add_department.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addDeptModalLabel">Agregar Departamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="dept_name_input" class="form-label">Nombre</label>
          <input name="name" id="dept_name_input" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS (bundle incluye Popper) - al final del body para asegurar que el DOM esté cargado -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
