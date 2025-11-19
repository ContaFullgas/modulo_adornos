<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

if ($_POST) {
    $item = (int)$_POST["item_id"];
    $dept = (int)$_POST["dept_id"];
    $qty = max(1,(int)$_POST["quantity"]);
    $user_id = (int)$_SESSION['user_id'];
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');

    // check availability
    $r = $conn->query("SELECT available_quantity FROM items WHERE id = $item");
    $it = $r->fetch_assoc();
    if(!$it || $it['available_quantity'] < $qty){
        die("No hay suficientes unidades disponibles.");
    }

    $conn->query("INSERT INTO reservations (item_id, dept_id, user_id, quantity, notes) VALUES ($item, $dept, $user_id, $qty, '$notes')");
    $conn->query("UPDATE items SET available_quantity = available_quantity - $qty WHERE id = $item");

    header("Location: reservations.php");
    exit;
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

    <form method="POST">
        <label>Departamento:</label>
        <select name="dept_id" class="form-control" required>
            <?php
            // Si el usuario es role=department, preseleccionar su dept y deshabilitar si quieres
            if(current_user()['role'] === 'department' && current_user()['department_id']){
                $d = current_user()['department_id'];
                $res = $conn->query("SELECT * FROM departments WHERE id = $d");
                $row = $res->fetch_assoc();
                echo "<option value=\"{$row['id']}\">".htmlspecialchars($row['name'])."</option>";
            } else {
                $deps = $conn->query("SELECT * FROM departments");
                while ($d = $deps->fetch_assoc()){
                    echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['name'])."</option>";
                }
            }
            ?>
        </select>

        <label>Adorno:</label>
        <select name="item_id" class="form-control" required>
            <?php
            $items = $conn->query("SELECT * FROM items WHERE available_quantity > 0");
            while ($i = $items->fetch_assoc()){
                echo "<option value=\"{$i['id']}\">".htmlspecialchars($i['name'])." (Disponibles: {$i['available_quantity']})</option>";
            }
            ?>
        </select>

        <label>Cantidad:</label>
        <input type="number" name="quantity" class="form-control" value="1" min="1" required>

        <label>Notas:</label>
        <input type="text" name="notes" class="form-control">

        <br>
        <button class="btn btn-primary">Reservar</button>
    </form>
</div>
</body>
</html>
