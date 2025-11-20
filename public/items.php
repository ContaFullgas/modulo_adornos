<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Adornos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      #add_preview, #edit_preview { max-width: 320px; max-height: 320px; display:block; margin-top:.5rem; }
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2 class="mb-4">🎄 Lista de Adornos</h2>

    <?php if(current_user()['role'] === 'admin'): ?>
      <!-- Botón que abre el modal para agregar adorno -->
      <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
        + Agregar Adorno
      </button>
    <?php endif; ?>

    <div class="row">
        <?php
        // Compruebo que exista la columna 'code'
        $colCheck = $conn->query("SHOW COLUMNS FROM items LIKE 'code'");
        if(!$colCheck || $colCheck->num_rows === 0){
            echo '<div class="alert alert-warning">La columna <strong>code</strong> no existe en la tabla <em>items</em>. Ejecuta el ALTER TABLE para crearla.</div>';
        }

        // Consulta y loop de items
        $res = $conn->query("SELECT * FROM items ORDER BY code");
        if(!$res){
            echo '<div class="alert alert-danger">Error en la consulta: ' . htmlspecialchars($conn->error) . '</div>';
        } else {
            while ($row = $res->fetch_assoc()):
                $item_id = (int)$row['id'];
                $code = htmlspecialchars($row['code'] ?? $row['id']);
                $desc = htmlspecialchars($row['description']);
                $avail = (int)$row['available_quantity'];
                $total = (int)$row['total_quantity'];
                $image = $row['image'];
        ?>
        <div class="col-md-4 mb-3">
          <div class="card h-100 shadow-sm">
            <?php if(!empty($image)): ?>
              <img src="uploads/<?= htmlspecialchars($image) ?>" class="card-img-top" alt="">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><strong>Código: </strong><?= $code ?></h5>
              <p class="card-text"><?= nl2br($desc) ?></p>
              <p class="mb-1"><strong>Total:</strong> <?= $total ?></p>
              <p class="mt-auto"><strong>Disponibles:</strong> <?= $avail ?></p>

              <div class="mt-3">
                <!-- Reservar -->
                <?php if($avail > 0): ?>
                  <button
                    class="btn btn-primary btn-reserve"
                    data-bs-toggle="modal"
                    data-bs-target="#reserveModal"
                    data-itemid="<?= $item_id ?>"
                    data-code="<?= $code ?>"
                    data-available="<?= $avail ?>"
                  >
                    Reservar
                  </button>
                <?php else: ?>
                  <button class="btn btn-secondary" disabled>Agotado</button>
                <?php endif; ?>

                <?php if(current_user()['role'] === 'admin'): ?>
                  <!-- Editar -->
                  <button
                    type="button"
                    class="btn btn-sm btn-warning ms-2 btn-edit"
                    data-bs-toggle="modal"
                    data-bs-target="#editItemModal"
                    data-id="<?= $item_id ?>"
                    data-code="<?= $code ?>"
                    data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                    data-total="<?= $total ?>"
                    data-available="<?= $avail ?>"
                    data-image="<?= htmlspecialchars($image, ENT_QUOTES) ?>"
                  >Editar</button>

                  <!-- Eliminar (form POST) -->
                  <form method="POST" action="item_action.php?action=delete" class="d-inline-block ms-2" onsubmit="return confirm('Eliminar adorno <?= addslashes($code) ?>?');">
                      <input type="hidden" name="id" value="<?= $item_id ?>">
                      <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                  </form>
                <?php endif; ?>
              </div>

            </div>
          </div>
        </div>
        <?php
            endwhile;
        }
        ?>
    </div>
</div>

<!-- ========== Modal: Agregar Adorno ========== -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <!-- form -> add_item.php (debes tener el endpoint preparado) -->
    <form method="POST" action="add_item.php" enctype="multipart/form-data" class="modal-content" id="addItemForm">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Adorno (Código)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Código / Folio</label>
          <input name="code" id="add_code" class="form-control" required placeholder="2, 2A, 12B">
          <div class="form-text">Debe empezar por números; opcional letras (regex: 1+ dígitos + letras opcionales).</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Cantidad total</label>
          <input type="number" name="total_quantity" id="add_total" class="form-control" min="1" value="1" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Foto (opcional) — preview</label>
          <input type="file" name="image" id="add_image" accept="image/*" class="form-control">
          <img id="add_preview" src="#" alt="Preview" style="display:none;">
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción (opcional)</label>
          <textarea name="description" id="add_description" class="form-control"></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Agregar</button>
      </div>
    </form>
  </div>
</div>

<!-- ========== Modal: Editar Adorno ========== -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="item_action.php?action=edit" enctype="multipart/form-data" class="modal-content" id="editItemForm">
      <div class="modal-header">
        <h5 class="modal-title">Editar Adorno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">

        <div class="mb-3">
          <label class="form-label">Código</label>
          <input name="code" id="edit_code" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Cantidad total</label>
          <input type="number" name="total_quantity" id="edit_total" class="form-control" min="1" required>
          <div class="form-text" id="edit_reserved_text"></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Foto actual</label>
          <div id="current_image_container">
            <img id="edit_preview" src="#" alt="Preview" style="display:none;">
          </div>
          <div class="form-text">Si subes una nueva imagen, la anterior será reemplazada.</div>
          <input type="hidden" name="existing_image" id="edit_existing_image">
          <input type="file" name="image" id="edit_image" accept="image/*" class="form-control mt-2">
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción (opcional)</label>
          <textarea name="description" id="edit_description" class="form-control"></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- ========== Modal Reserva (reutilizable) ========== -->
<div class="modal fade" id="reserveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="reserveForm" method="post" action="reserve.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reservar adorno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="item_id" id="modal_item_id">
        <div class="mb-2">
          <label class="form-label">Código</label>
          <input type="text" id="modal_item_code" class="form-control" readonly>
        </div>

        <div class="mb-2">
          <label class="form-label">Departamento</label>
          <select name="dept_id" id="modal_dept_select" class="form-select" required>
            <option value="">-- seleccionar --</option>
            <?php
            $deps = $conn->query("SELECT id, name FROM departments ORDER BY name");
            while($d = $deps->fetch_assoc()){
                echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['name'])."</option>";
            }
            ?>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Cantidad</label>
          <input type="number" name="quantity" id="modal_qty" class="form-control" value="1" min="1" required>
          <div class="form-text" id="modal_available_text"></div>
        </div>

        <div class="mb-2">
          <label class="form-label">Notas (opcional)</label>
          <input type="text" name="notes" class="form-control">
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Reservar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ----- Add item: image preview & code sanitization -----
const addImage = document.getElementById('add_image');
const addPreview = document.getElementById('add_preview');
if(addImage){
  addImage.addEventListener('change', function(){
    const f = this.files[0];
    if(!f){ addPreview.style.display='none'; addPreview.src='#'; return; }
    if(!f.type.startsWith('image/')) { addPreview.style.display='none'; return; }
    const reader = new FileReader();
    reader.onload = function(e){ addPreview.src = e.target.result; addPreview.style.display = 'block'; };
    reader.readAsDataURL(f);
  });
}
// uppercase code & trim spaces
const addCode = document.getElementById('add_code');
if(addCode){
  addCode.addEventListener('input', ()=> addCode.value = addCode.value.toUpperCase().replace(/\s+/g,'')); 
}

// Simple client-side validation before submit
document.getElementById('addItemForm').addEventListener('submit', function(e){
  const code = addCode.value.trim();
  if(!/^\d+[A-Za-z]*$/.test(code)){
    e.preventDefault();
    alert('Código inválido. Debe ser: dígitos seguidos opcionalmente de letras (ej. 2, 2A).');
    return false;
  }
  return true;
});

// ----- Edit modal: rellenar campos y preview -----
var editModal = document.getElementById('editItemModal');
editModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var id = button.getAttribute('data-id');
  var code = button.getAttribute('data-code');
  var description = button.getAttribute('data-description') || '';
  var total = button.getAttribute('data-total') || '1';
  var available = button.getAttribute('data-available') || '0';
  var image = button.getAttribute('data-image') || '';

  document.getElementById('edit_id').value = id;
  document.getElementById('edit_code').value = code;
  document.getElementById('edit_total').value = total;
  document.getElementById('edit_description').value = description;
  document.getElementById('edit_existing_image').value = image;

  // Mostrar preview de imagen actual si existe
  var editPreview = document.getElementById('edit_preview');
  if(image){
    editPreview.src = 'uploads/' + image;
    editPreview.style.display = 'block';
  } else {
    editPreview.src = '#';
    editPreview.style.display = 'none';
  }

  // Mostrar texto con cuántos están reservados (reservados = total - available)
  var reserved = parseInt(total,10) - parseInt(available,10);
  if(reserved < 0) reserved = 0;
  document.getElementById('edit_reserved_text').textContent = 'Reservados: ' + reserved + ' — Disponibles ahora: ' + available;
});

// Preview when choosing new image in edit modal
const editImage = document.getElementById('edit_image');
const editPreview = document.getElementById('edit_preview');
if(editImage){
  editImage.addEventListener('change', function(){
    const f = this.files[0];
    if(!f){ 
      // if no file selected, show existing image (already set when modal opened)
      return;
    }
    if(!f.type.startsWith('image/')) { return; }
    const reader = new FileReader();
    reader.onload = function(e){ editPreview.src = e.target.result; editPreview.style.display = 'block'; };
    reader.readAsDataURL(f);
  });
}

// ----- Reserve modal behavior (same as antes) -----
var reserveModal = document.getElementById('reserveModal');
reserveModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var itemId = button.getAttribute('data-itemid');
  var code = button.getAttribute('data-code');
  var available = parseInt(button.getAttribute('data-available') || '0', 10);

  document.getElementById('modal_item_id').value = itemId;
  document.getElementById('modal_item_code').value = code;
  document.getElementById('modal_qty').value = Math.min(1, available);
  document.getElementById('modal_qty').max = Math.max(1, available);
  document.getElementById('modal_available_text').textContent = 'Disponibles: ' + available;

  <?php if(current_user()['role'] === 'department' && current_user()['department_id']): ?>
    var deptSelect = document.getElementById('modal_dept_select');
    deptSelect.value = "<?= (int)current_user()['department_id'] ?>";
    deptSelect.disabled = true;
    var existingHidden = document.getElementById('modal_dept_hidden');
    if(!existingHidden){
      var h = document.createElement('input');
      h.type = 'hidden';
      h.name = 'dept_id';
      h.id = 'modal_dept_hidden';
      h.value = "<?= (int)current_user()['department_id'] ?>";
      document.getElementById('reserveForm').appendChild(h);
    } else {
      existingHidden.value = "<?= (int)current_user()['department_id'] ?>";
    }
  <?php else: ?>
    var deptSelect = document.getElementById('modal_dept_select');
    if(deptSelect.disabled){
      deptSelect.disabled = false;
      var existingHidden = document.getElementById('modal_dept_hidden');
      if(existingHidden) existingHidden.remove();
    }
  <?php endif; ?>
});

// validate reserve qty on submit
document.getElementById('reserveForm').addEventListener('submit', function(e){
  var qty = parseInt(document.getElementById('modal_qty').value || '0', 10);
  var max = parseInt(document.getElementById('modal_qty').max || '0', 10);
  if(qty < 1 || qty > max){
    e.preventDefault();
    alert('Cantidad inválida. Debe ser entre 1 y ' + max);
    return false;
  }
});
</script>
</body>
</html>
