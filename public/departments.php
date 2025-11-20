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
        <thead>
          <tr>
            <th>Nombre</th>
            <?php if(current_user()['role'] === 'admin'): ?>
              <th style="width:190px">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT * FROM departments ORDER BY name");
        while ($row = $result->fetch_assoc()):
            $id = (int)$row['id'];
            $name = htmlspecialchars($row["name"]);
        ?>
        <tr>
            <td><?= $name ?></td>
            <?php if(current_user()['role'] === 'admin'): ?>
            <td>
              <!-- Edit: abre modal y pasa data-attributes -->
              <button
                class="btn btn-sm btn-warning btn-edit"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#editDeptModal"
                data-id="<?= $id ?>"
                data-name="<?= $name ?>"
              >
                Editar
              </button>

              <!-- Delete: formulario POST (para evitar GET deletes). Confirm JS -->
              <form method="POST" action="department_action.php?action=delete" style="display:inline-block" onsubmit="return confirm('Eliminar departamento «<?= addslashes($row['name']) ?>»?');">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
              </form>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar departamento -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="department_action.php?action=create" class="modal-content">
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

<!-- Modal para EDITAR departamento (rellenado por JS) -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="department_action.php?action=edit" class="modal-content" id="editDeptForm">
      <div class="modal-header">
        <h5 class="modal-title" id="editDeptModalLabel">Editar Departamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit_dept_id">
        <div class="mb-3">
          <label for="edit_dept_name" class="form-label">Nombre</label>
          <input name="name" id="edit_dept_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Rellenar modal editar cuando se abre
var editModal = document.getElementById('editDeptModal');
editModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var id = button.getAttribute('data-id');
  var name = button.getAttribute('data-name');

  document.getElementById('edit_dept_id').value = id;
  document.getElementById('edit_dept_name').value = name;
});
</script>
</body>
</html>
