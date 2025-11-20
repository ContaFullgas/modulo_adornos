<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Celebraciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2 class="mb-4">🎉 Celebraciones</h2>

    <?php if(current_user()['role'] === 'admin'): ?>
      <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addCelebrationModal">
        + Agregar Celebración
      </button>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
          <tr>
            <th>Nombre</th>
            <?php if(current_user()['role'] === 'admin'): ?>
              <th style="width:170px">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $res = $conn->query("SELECT * FROM celebrations ORDER BY name");
        while ($row = $res->fetch_assoc()):
            $id = (int)$row['id'];
            $name = htmlspecialchars($row['name']);
        ?>
        <tr>
            <td><?= $name ?></td>
            <?php if(current_user()['role'] === 'admin'): ?>
            <td>
              <!-- Editar: abre modal -->
              <button
                class="btn btn-sm btn-warning"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#editCelebrationModal"
                data-id="<?= $id ?>"
                data-name="<?= $name ?>"
              >Editar</button>

              <!-- Eliminar: POST -->
              <form method="POST" action="celebration_action.php?action=delete" style="display:inline-block" onsubmit="return confirm('Eliminar celebración «<?= addslashes($row['name']) ?>»?');">
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

<!-- Modal Agregar -->
<div class="modal fade" id="addCelebrationModal" tabindex="-1" aria-labelledby="addCelebrationLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="celebration_action.php?action=create" class="modal-content" id="addCelebrationForm">
      <div class="modal-header">
        <h5 class="modal-title" id="addCelebrationLabel">Agregar Celebración</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input name="name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editCelebrationModal" tabindex="-1" aria-labelledby="editCelebrationLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="celebration_action.php?action=edit" class="modal-content" id="editCelebrationForm">
      <div class="modal-header">
        <h5 class="modal-title" id="editCelebrationLabel">Editar Celebración</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit_celebration_id">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input name="name" id="edit_celebration_name" class="form-control" required>
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
// Rellenar modal editar
var editModal = document.getElementById('editCelebrationModal');
editModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var id = button.getAttribute('data-id');
  var name = button.getAttribute('data-name');
  document.getElementById('edit_celebration_id').value = id;
  document.getElementById('edit_celebration_name').value = name;
});
</script>
</body>
</html>
