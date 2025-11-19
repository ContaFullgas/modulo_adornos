<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

$err = '';
if ($_POST) {
    $item = isset($_POST["item_id"]) ? (int)$_POST["item_id"] : 0;
    $dept = isset($_POST["dept_id"]) ? (int)$_POST["dept_id"] : 0;
    $qty = max(1,(int)$_POST["quantity"]);
    $user_id = (int)$_SESSION['user_id'];
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');

    if(!$item || !$dept){
        $err = "Faltan datos (item o departamento).";
    } else {
        // check availability using prepared statement
        $stmt = $conn->prepare("SELECT available_quantity FROM items WHERE id = ?");
        $stmt->bind_param("i", $item);
        $stmt->execute();
        $res = $stmt->get_result();
        $it = $res->fetch_assoc();
        if(!$it) {
            $err = "Adorno no encontrado.";
        } elseif($it['available_quantity'] < $qty) {
            $err = "No hay suficientes unidades disponibles. Disponibles: " . (int)$it['available_quantity'];
        }
    }

    if(!$err){
        // insert reservation (prepared)
        $ins = $conn->prepare("INSERT INTO reservations (item_id, dept_id, user_id, quantity, notes) VALUES (?, ?, ?, ?, ?)");
        $ins->bind_param("iiiis", $item, $dept, $user_id, $qty, $notes);
        $okIns = $ins->execute();

        if($okIns){
            // update stock
            $upd = $conn->prepare("UPDATE items SET available_quantity = available_quantity - ? WHERE id = ?");
            $upd->bind_param("ii", $qty, $item);
            $upd->execute();

            header("Location: reservations.php");
            exit;
        } else {
            $err = "Error al crear la reserva: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reservar Adorno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2>Reservar Adorno</h2>

    <?php if($err): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="POST" id="reserveForm">
        <label>Departamento:</label>
        <select name="dept_id" id="deptSelect" class="form-control" required <?= (current_user()['role'] === 'department' ? 'disabled' : '') ?>>
            <?php
            // Si el usuario es role=department, preseleccionar su dept y deshabilitar
            if(current_user()['role'] === 'department' && current_user()['department_id']){
                $d = (int)current_user()['department_id'];
                $res = $conn->prepare("SELECT id, name FROM departments WHERE id = ?");
                $res->bind_param("i",$d);
                $res->execute();
                $rres = $res->get_result();
                $row = $rres->fetch_assoc();
                if($row){
                    echo "<option value=\"{$row['id']}\">".htmlspecialchars($row['name'])."</option>";
                }
            } else {
                $deps = $conn->query("SELECT id, name FROM departments ORDER BY name");
                while ($d = $deps->fetch_assoc()){
                    echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['name'])."</option>";
                }
            }
            ?>
        </select>

        <label class="mt-3">Adorno:</label>
        <select name="item_id" id="itemSelect" class="form-control" required>
            <?php
            // Mostrar code (no name) y cantidad disponible; ordenar por code
            $items = $conn->query("SELECT id, code, description, available_quantity FROM items WHERE available_quantity > 0 ORDER BY code");
            while ($i = $items->fetch_assoc()){
                $code = htmlspecialchars($i['code'] ?: $i['id']);
                $desc = htmlspecialchars($i['description']);
                $avail = (int)$i['available_quantity'];
                // data-available para JS, muestra code y cantidad
                echo "<option value=\"{$i['id']}\" data-available=\"{$avail}\">{$code} - {$desc} (Disponibles: {$avail})</option>";
            }
            ?>
        </select>

        <label class="mt-3">Cantidad:</label>
        <input type="number" name="quantity" id="qtyInput" class="form-control" value="1" min="1" required>

        <label class="mt-3">Notas:</label>
        <input type="text" name="notes" class="form-control">

        <br>
        <button class="btn btn-primary mt-3">Reservar</button>
    </form>
</div>

<script>
// Ajustar max en cantidad según item seleccionado
const itemSelect = document.getElementById('itemSelect');
const qtyInput = document.getElementById('qtyInput');

function updateMaxFromSelected(){
    const opt = itemSelect.options[itemSelect.selectedIndex];
    const avail = parseInt(opt.getAttribute('data-available') || '0', 10);
    qtyInput.max = Math.max(1, avail);
    if(parseInt(qtyInput.value,10) > avail) qtyInput.value = avail;
}

// inicializar
if(itemSelect) {
    updateMaxFromSelected();
    itemSelect.addEventListener('change', updateMaxFromSelected);
}

// Si el select de dept está disabled (usuario departamento), aseguramos que su value se envíe al submit.
// Porque un select disabled no envía value, creamos un hidden con el mismo value.
const deptSelect = document.getElementById('deptSelect');
if(deptSelect && deptSelect.disabled){
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = deptSelect.name;
    hidden.value = deptSelect.value;
    document.getElementById('reserveForm').appendChild(hidden);
}
</script>
</body>
</html>
