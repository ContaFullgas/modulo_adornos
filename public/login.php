<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$err = '';
if($_POST){
    $username = trim($_POST['username']);
    $password = $_POST['password']; // texto plano

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    if($user && $user['password'] === $password){
        // login OK (sin hash)
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $err = "Usuario o contraseña incorrecta.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card mx-auto" style="max-width:420px;">
    <div class="card-body">
      <h4 class="card-title mb-3">Iniciar sesión</h4>
      <?php if($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-2"><input name="username" class="form-control" placeholder="Usuario" required></div>
        <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Contraseña" required></div>
        <button class="btn btn-primary w-100">Entrar</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
