<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();

/* GET USERS */
$users = [];
$res = $conn->query("
    SELECT u.*, d.name AS department_name
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.id
    ORDER BY u.created_at DESC
");
while($r = $res->fetch_assoc()) $users[] = $r;

/* GET DEPARTMENTS FOR SELECT */
$departments = [];
$dr = $conn->query("SELECT * FROM departments ORDER BY name");
while($d = $dr->fetch_assoc()) $departments[] = $d;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="container py-4">

    <h2>Usuarios</h2>

    <!-- Botón para abrir modal -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalUser">
        + Nuevo usuario
    </button>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Dept</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['role'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['department_name'] ?? '') ?></td>
                <td>
                    <button 
                        class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalUser"
                        data-id="<?= $u['id'] ?>"
                        data-username="<?= htmlspecialchars($u['username'] ?? '') ?>"
                        data-fullname="<?= htmlspecialchars($u['full_name'] ?? '') ?>"
                        data-role="<?= $u['role'] ?>"
                        data-dept="<?= $u['department_id'] ?>"
                    >
                        Editar
                    </button>

                    <form method="post" action="admin_user_action.php?action=delete" style="display:inline-block">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Eliminar usuario?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- MODAL PARA CREAR / EDITAR -->
<div class="modal fade" id="modalUser" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="post" action="admin_user_action.php">

    <div class="modal-header">
        <h5 class="modal-title">Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <input type="hidden" name="id" id="user_id">

        <div class="mb-2">
            <label>Usuario:</label>
            <input name="username" id="user_username" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Nombre completo:</label>
            <input name="full_name" id="user_fullname" class="form-control">
        </div>

        <div class="mb-2">
            <label>Contraseña:</label>
            <input name="password" id="user_password" type="text" class="form-control">
            <!-- <small class="text-muted">Si dejas vacío, no se modifica.</small> -->
        </div>

        <div class="mb-2">
            <label>Rol:</label>
            <select name="role" id="user_role" class="form-select">
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
        </div>

        <div class="mb-2">
            <label>Departamento:</label>
            <select name="department_id" id="user_department" class="form-select">
                <option value="">-- Ninguno --</option>
                <?php foreach($departments as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <div class="modal-footer">
        <button class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    </div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// CARGAR DATOS EN EL MODAL
const modal = document.getElementById('modalUser');
modal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;

    const id = button.getAttribute('data-id') || '';
    document.getElementById('user_id').value = id;

    document.getElementById('user_username').value = button.getAttribute('data-username') || '';
    document.getElementById('user_fullname').value = button.getAttribute('data-fullname') || '';
    document.getElementById('user_role').value = button.getAttribute('data-role') || 'usuario';
    document.getElementById('user_department').value = button.getAttribute('data-dept') || '';

    document.getElementById('user_password').value = '';
});
</script>

</body>
</html>
