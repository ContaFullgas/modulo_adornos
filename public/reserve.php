<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = isset($_POST["item_id"]) ? (int)$_POST["item_id"] : 0;
    $qty = max(1, (int)($_POST["quantity"] ?? 1));
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');

    // Forzar dept_id desde sesión si el usuario tiene rol 'usuario'
    if (current_user()['role'] === 'usuario') {
        $dept = (int)(current_user()['department_id'] ?? 0);
    } else {
        // admin u otros roles: aceptar lo enviado (pero validar)
        $dept = isset($_POST["dept_id"]) ? (int)$_POST["dept_id"] : 0;
    }

    if (!$item || !$dept) {
        $err = "Faltan datos (item o departamento).";
    } else {
        // check availability using prepared statement
        $stmt = $conn->prepare("SELECT available_quantity FROM items WHERE id = ?");
        $stmt->bind_param("i", $item);
        $stmt->execute();
        $res = $stmt->get_result();
        $it = $res->fetch_assoc();
        $stmt->close();

        if (!$it) {
            $err = "Adorno no encontrado.";
        } elseif ((int)$it['available_quantity'] < $qty) {
            $err = "No hay suficientes unidades disponibles. Disponibles: " . (int)$it['available_quantity'];
        }
    }

    if (!$err) {
        // insert reservation (prepared)
        $ins = $conn->prepare("INSERT INTO reservations (item_id, dept_id, user_id, quantity, notes, status, reserved_at) VALUES (?, ?, ?, ?, ?, 'reservado', NOW())");
        $ins->bind_param("iiiis", $item, $dept, $user_id, $qty, $notes);
        $okIns = $ins->execute();

        if ($okIns) {
            $ins->close();
            // update stock
            $upd = $conn->prepare("UPDATE items SET available_quantity = available_quantity - ? WHERE id = ?");
            $upd->bind_param("ii", $qty, $item);
            $upd->execute();
            $upd->close();

            header("Location: reservations.php");
            exit;
        } else {
            $err = "Error al crear la reserva: " . htmlspecialchars($conn->error);
            $ins->close();
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

        <?php
        // Si el usuario tiene rol 'usuario' preseleccionamos y bloqueamos, además añadimos hidden input
        if (current_user()['role'] === 'usuario' && !empty(current_user()['department_id'])):
            $myDeptId = (int)current_user()['department_id'];
            // obtener nombre del dept
            $stmtd = $conn->prepare("SELECT id, name FROM departments WHERE id = ?");
            $stmtd->bind_param("i", $myDeptId);
            $stmtd->execute();
            $dr = $stmtd->get_result()->fetch_assoc();
            $stmtd->close();
            $deptName = $dr['name'] ?? '';
        ?>
            <select name="dept_id" id="deptSelect" class="form-control" required disabled>
                <option value="<?= $myDeptId ?>"><?= htmlspecialchars($deptName) ?></option>
            </select>
            <!-- Hidden para asegurar que el dept_id se envía -->
            <input type="hidden" name="dept_id" value="<?= $myDeptId ?>">
        <?php else: ?>
            <select name="dept_id" id="deptSelect" class="form-control" required>
                <?php
                $deps = $conn->query("SELECT id, name FROM departments ORDER BY name");
                while ($d = $deps->fetch_assoc()){
                    echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['name'])."</option>";
                }
                ?>
            </select>
        <?php endif; ?>

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

// No es necesario crear hidden via JS ya que lo generamos en el HTML cuando el select está disabled
</script>
</body>
</html>
