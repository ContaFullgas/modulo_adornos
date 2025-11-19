<?php
// add_item.php (actualizado)
require_once __DIR__ . '/../config/auth.php';
require_login();
if(current_user()['role'] !== 'admin') { echo "Acceso denegado"; exit; }

$err = '';
$success = '';

if ($_POST) {
    // Recoger y sanitizar
    $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
    $desc = $conn->real_escape_string($_POST["description"] ?? '');
    $total = max(1, (int)($_POST["total_quantity"] ?? 1));

    // Validación de formato
    if(!preg_match('/^\d+[A-Za-z]*$/', $code)) {
        $err = "Código inválido. Debe empezar con números y opcionalmente letras (ej. 2, 2A, 12B).";
    }

    // Check unicidad
    if(!$err) {
        $stmt = $conn->prepare("SELECT id FROM items WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->fetch_assoc()) {
            $err = "El código ya existe. Debes usar un código/folio irrepetible.";
        }
    }

    // Procesar imagen si no hay errores
    $image_name = "";
    if(!$err && !empty($_FILES["image"]["name"])) {
        if(!is_dir(__DIR__ . "/uploads")) mkdir(__DIR__ . "/uploads", 0755, true);
        $origName = basename($_FILES["image"]["name"]);
        // Sanear nombre y evitar colisiones: timestamp + sanitized name
        $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', $origName);
        $image_name = time() . "_" . $safe;
        $target = __DIR__ . "/uploads/" . $image_name;
        if(!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
            $err = "No se pudo guardar la imagen en el servidor.";
        }
    }

    // Insertar en DB
    if(!$err) {
        // Usamos prepared statement
        $stmt = $conn->prepare("INSERT INTO items (code, description, total_quantity, available_quantity, image) VALUES (?, ?, ?, ?, ?)");
        $avail = $total;
        $stmt->bind_param("ssiis", $code, $desc, $total, $avail, $image_name);
        if($stmt->execute()) {
            $success = "Adorno agregado correctamente con código {$code}.";
            // limpiar formulario si quieres:
            $_POST = [];
        } else {
            $err = "Error al guardar en la base de datos: " . $conn->error;
            // si falló y subimos imagen, puedes eliminarla si quieres
            if($image_name && file_exists(__DIR__ . "/uploads/" . $image_name)) {
                @unlink(__DIR__ . "/uploads/" . $image_name);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Agregar Adorno (por código)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      #preview { max-width: 240px; max-height: 240px; display:block; margin-top:0.5rem; }
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container py-4">
    <h2>Agregar Adorno (Código / Folio)</h2>

    <?php if($err): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="addForm">
        <div class="mb-3">
            <label class="form-label">Código / Folio (único) *</label>
            <input name="code" id="code" class="form-control" required
                   value="<?= isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '' ?>"
                   placeholder="ej. 2, 2A, 12B">
            <div class="form-text">Formato: un número seguido opcionalmente por letras (ej. 2, 2A, 2B).</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción (opcional)</label>
            <textarea name="description" class="form-control"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Cantidad total *</label>
            <input type="number" name="total_quantity" class="form-control" min="1" required
                   value="<?= isset($_POST['total_quantity']) ? (int)$_POST['total_quantity'] : 1 ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Foto (opcional) — se mostrará preview</label>
            <input type="file" name="image" id="imageInput" accept="image/*" class="form-control">
            <img id="preview" src="#" alt="Preview" style="display:none;">
        </div>

        <button class="btn btn-primary">Agregar Adorno</button>
    </form>
</div>

<script>
// Imagen preview
const imageInput = document.getElementById('imageInput');
const preview = document.getElementById('preview');
imageInput.addEventListener('change', function(e){
    const file = this.files[0];
    if(!file) { preview.style.display = 'none'; preview.src = '#'; return; }
    if(!file.type.startsWith('image/')) {
        preview.style.display = 'none';
        preview.src = '#';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(ev) {
        preview.src = ev.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});

// Opcional: forzar uppercase en código, quitar espacios
const codeInput = document.getElementById('code');
codeInput.addEventListener('input', function(){ 
    this.value = this.value.toUpperCase().replace(/\s+/g, '');
});
</script>
</body>
</html>
