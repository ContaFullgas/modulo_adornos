<?php include("../config/auth.php"); require_login(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Navidad - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h1 class="mb-4">🎁 Sistema de Adornos Navideños</h1>
    <p>Bienvenido <?= htmlspecialchars(current_user()['username']) ?>.</p>
    <div class="row">
      <div class="col-md-6">
        <div class="card p-3 mb-3">
          <h5>Inventario</h5>
          <a href="items.php" class="btn btn-sm btn-primary">Ver Adornos</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3 mb-3">
          <h5>Reservas</h5>
          <a href="reservations.php" class="btn btn-sm btn-primary">Ver Reservas</a>
        </div>
      </div>
    </div>
</div>

</body>
</html>
