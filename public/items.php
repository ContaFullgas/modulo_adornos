<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Adornos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2 class="mb-4">🎄 Lista de Adornos</h2>
    <?php if(current_user()['role'] === 'admin'): ?>
      <a href="add_item.php" class="btn btn-success mb-3">+ Agregar Adorno</a>
    <?php endif; ?>

    <div class="row">
        <?php
        $res = $conn->query("SELECT * FROM items ORDER BY name");
        while ($row = $res->fetch_assoc()):
        ?>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <?php if(!empty($row['image'])): ?>
              <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
              <p class="mt-auto"><strong>Disponibles:</strong> <?= (int)$row['available_quantity'] ?></p>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
