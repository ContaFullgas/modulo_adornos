<?php
require_once __DIR__ . '/../config/auth.php';
$user = is_logged_in() ? current_user() : null;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">🎄 Navidad</a>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="items.php">Adornos</a></li>
        <li class="nav-item"><a class="nav-link" href="departments.php">Departamentos</a></li>
        <li class="nav-item"><a class="nav-link" href="celebrations.php">Celebraciones</a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="reserve.php">Reservar</a></li> -->
        <li class="nav-item"><a class="nav-link" href="reservations.php">Reservas</a></li>
        <?php if($user && $user['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="admin_users.php">Usuarios</a></li>
          <li class="nav-item"><a class="nav-link" href="returns.php">Devoluciones</a></li>
          <li class="nav-item"><a class="nav-link" href="report_pdf.php?type=reservations">Generar PDF</a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if($user): ?>
          <li class="nav-item"><span class="nav-link">Hola, <?= htmlspecialchars($user['username']) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Salir</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Entrar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
