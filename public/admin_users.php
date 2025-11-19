<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();

$users = [];
$res = $conn->query("SELECT u.*, d.name as department_name FROM users u LEFT JOIN departments d ON u.department_id=d.id ORDER BY u.created_at DESC");
while($r = $res->fetch_assoc()) $users[] = $r;
$departments = [];
$dr = $conn->query("SELECT * FROM departments ORDER BY name");
while($d = $dr->fetch_assoc()) $departments[] = $d;
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<?php include("navbar.php"); ?>
<div class="container py-4">
  <h2>Usuarios</h2>

  <h4>Crear usuario (contraseña en texto plano)</h4>
  <form method="post" action="admin_user_action.php?action=create" class="row g-2 mb-4">
    <div class="col-md-3"><input name="username" class="form-control" placeholder="Usuario" required></div>
    <div class="col-md-3"><input name="password" type="text" class="form-control" placeholder="Contraseña (texto plano)" required></div>
    <div class="col-md-3">
      <select name="role" class="form-select">
        <option value="department">Departamento</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="department_id" class="form-select">
        <option value="">-- Sin departamento --</option>
        <?php foreach($departments as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12"><button class="btn btn-success">Crear</button></div>
  </form>

  <h4>Listado</h4>
  <table class="table">
    <thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Dept</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php foreach($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= $u['role'] ?></td>
          <td><?= htmlspecialchars($u['department_name']) ?></td>
          <td>
            <a class="btn btn-sm btn-warning" href="admin_user_action.php?action=edit&id=<?= $u['id'] ?>">Editar</a>
            <a class="btn btn-sm btn-danger" href="admin_user_action.php?action=delete&id=<?= $u['id'] ?>" onclick="return confirm('Eliminar usuario?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
