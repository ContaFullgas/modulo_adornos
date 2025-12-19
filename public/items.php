<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

// --- LÓGICA DE PAGINACIÓN ---
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Inventario de Adornos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --primary-color: #3b82f6;
        --secondary-bg: #f8f9fa;
        --card-radius: 16px;
    }

    body {
        background-color: #f0f2f5;
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding-top: 110px;
    }

    /* Estilos Card */
    .item-card {
        border: none;
        border-radius: var(--card-radius);
        background: white;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02), 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        height: 100%;
    }

    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .card-img-wrapper {
        position: relative;
        height: 240px;
        width: 100%;
        overflow: hidden;
        background-color: #e9ecef;
    }

    .card-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .item-card:hover .card-img-wrapper img {
        transform: scale(1.05);
    }

    .item-card.out-of-stock .card-img-wrapper img {
        filter: grayscale(100%);
        opacity: 0.7;
    }

    .badge-overlay {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 10;
        font-weight: 500;
        backdrop-filter: blur(4px);
        background-color: rgba(255, 255, 255, 0.9);
        color: #0f172a;
        border-radius: 20px;
        padding: 6px 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 0.85rem;
    }

    .status-overlay {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 10;
    }

    .card-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
    }

    .item-code {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .item-desc {
        font-size: 0.9rem;
        color: #64748b;
        flex-grow: 1;
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    .stats-container {
        background-color: #f1f5f9;
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }

    .stat-item {
        text-align: center;
        font-size: 0.85rem;
        color: #475569;
    }

    .stat-value {
        display: block;
        font-weight: bold;
        font-size: 1.1rem;
        color: #0f172a;
    }

    .reserved-info {
        font-size: 0.8rem;
        background-color: #fff1f2;
        color: #be123c;
        padding: 8px;
        border-radius: 8px;
        border: 1px dashed #fda4af;
        margin-bottom: 1rem;
    }

    .action-buttons {
        margin-top: auto;
        display: flex;
        gap: 8px;
    }

    .btn-reserve {
        flex: 1;
        font-weight: 600;
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .btn-reserve:hover {
        background-color: #2563eb;
    }

    /* Previews */
    #add_preview,
    #edit_preview {
        max-width: 100%;
        height: 200px;
        object-fit: contain;
        display: block;
        margin-top: .5rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    /* Estilos Paginación */
    .pagination .page-link {
        color: #3b82f6;
        border: none;
        margin: 0 5px;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .pagination .page-item.active .page-link {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    }

    .pagination .page-link:hover {
        background-color: #eff6ff;
    }

    /* === BOTÓN AGREGAR (VERDE - INDEPENDIENTE) === */
    .btn-add-custom {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        height: 100%;
    }

    .btn-add-custom:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* === BARRA FILTRO (BLANCA) === */
    .filter-bar {
        background: white;
        padding: 0.6rem 1rem;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        flex-grow: 1;
    }

    /* === MODAL ESTILIZADO === */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    .modal-header-custom {
        padding: 1.5rem 1.5rem 0 1.5rem;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .btn-close-custom {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 10;
    }

    .modal-icon-circle {
        width: 60px;
        height: 60px;
        background-color: #ecfdf5;
        color: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        animation: pulse-soft 2s infinite;
    }

    .modal-icon-circle.warning {
        background-color: #fffbeb;
        color: #f59e0b;
    }

    @keyframes pulse-soft {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.2); }
        70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .modal-title-custom {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.2rem;
    }

    .modal-subtitle {
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .modal-body-custom {
        padding: 0 2rem 2rem 2rem;
    }

    .form-floating>.form-control,
    .form-floating>.form-select {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
    }

    .form-floating>.form-control:focus,
    .form-floating>.form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    .form-control.edit-input:focus,
    .form-select.edit-input:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    }

    .form-floating>label {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .btn-save-modal {
        width: 100%;
        background-color: #10b981;
        color: white;
        padding: 0.8rem;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        transition: all 0.3s;
    }

    .btn-save-modal:hover {
        background-color: #059669;
        transform: translateY(-2px);
    }

    .btn-save-modal.warning {
        background-color: #f59e0b;
    }

    .btn-save-modal.warning:hover {
        background-color: #d97706;
    }

    /* Modal imagen */
    #imageModal .modal-content { background: #0b1220; border: 0; }
    #imageModal .modal-header { border: 0; }
    #imageModal .modal-body { padding: 0; }
    #imageModal .img-stage{
        position: relative;
        width: 100%;
        height: min(80vh, 760px);
        overflow: hidden;
        background: #0b1220;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #imageModal #modalImage{
        width: 100%;
        height: 100%;
        object-fit: contain;
        user-select: none;
        -webkit-user-drag: none;
        cursor: default;
    }

    .text-truncate-2{
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Animación suave para el modal */
    #imageModal .modal-content {
        animation: slideIn 0.3s ease-out;
        border-radius: 20px;
        overflow: hidden;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Backdrop oscuro */
    #imageModal.modal.show {
        background-color: rgba(0, 0, 0, 0.75);
    }

    /* Efecto hover en el botón de cerrar */
    #imageModal .btn-close-white {
        transition: transform 0.2s, opacity 0.2s;
        opacity: 0.9;
    }

    #imageModal .btn-close-white:hover {
        transform: rotate(90deg);
        opacity: 1;
    }

    /* Imagen redondeada con sombra */
    #imageModal #modalImage {
        transition: transform 0.3s ease;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    #imageModal #modalImage:hover {
        transform: scale(1.02);
    }

    /* Efecto glow en el icono */
    #imageModal .modal-title .fa-image {
        filter: drop-shadow(0 0 6px rgba(76, 175, 80, 0.6));
    }

    </style>
</head>

<body>
    <?php include("navbar.php"); ?>

    <div class="container py-4">

        <?php
        $sel = isset($_GET['celebration']) ? (int)$_GET['celebration'] : 0;
        $celebs = $conn->query("SELECT id, name FROM celebrations ORDER BY name");
        ?>

        <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-4">

            <div class="filter-bar">
                <form method="get" class="d-flex align-items-center gap-2 w-100">
                    <div class="input-group border-0">
                        <span class="input-group-text bg-transparent border-0 text-secondary ps-0">
                            <i class="fas fa-filter"></i>
                        </span>
                        <select name="celebration"
                            class="form-select border-0 bg-transparent fw-semibold text-dark shadow-none"
                            onchange="this.form.submit()" style="cursor: pointer; min-width: 200px;">
                            <option value="0">Todas las celebraciones</option>
                            <?php
                            if($celebs){
                                while($c = $celebs->fetch_assoc()){
                                    $cid = (int)$c['id'];
                                    $selAttr = ($sel === $cid) ? ' selected' : '';
                                    echo "<option value=\"{$cid}\"{$selAttr}>".htmlspecialchars($c['name'])."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <?php if($sel): ?>
                    <a href="items.php"
                        class="btn btn-sm btn-light text-danger rounded-circle d-flex align-items-center justify-content-center"
                        style="width:30px; height:30px; background:#fee2e2;" title="Limpiar">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if(current_user()['role'] === 'admin'): ?>
            <div>
                <button type="button" class="btn-add-custom" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus"></i> <span>Nuevo Adorno</span>
                </button>
            </div>
            <?php endif; ?>

        </div>

        <div class="row g-4">
            <?php
            $where = $sel ? "WHERE celebration_id = " . intval($sel) : "";

            // Paginación y Totales
            $total_result = $conn->query("SELECT COUNT(*) as count FROM items $where");
            $total_items = $total_result->fetch_assoc()['count'];
            $total_pages = ceil($total_items / $items_per_page);

            // Mapeo Reservas
            $reserved_map = [];
            $res_group = $conn->query("
                SELECT r.item_id, d.id AS dept_id, d.name AS dept_name, SUM(r.quantity) AS qty
                FROM reservations r
                JOIN departments d ON d.id = r.dept_id
                WHERE LOWER(r.status) = 'reservado'
                GROUP BY r.item_id, d.id, d.name
            ");
            if($res_group){
                while($rg = $res_group->fetch_assoc()){
                    $iid = (int)$rg['item_id'];
                    if(!isset($reserved_map[$iid])) $reserved_map[$iid] = [];
                    $reserved_map[$iid][] = [
                        'dept_id' => (int)$rg['dept_id'],
                        'dept_name' => $rg['dept_name'],
                        'qty' => (int)$rg['qty']
                    ];
                }
            }

            $res = $conn->query("SELECT * FROM items $where ORDER BY code LIMIT $offset, $items_per_page");

            if(!$res || $res->num_rows === 0){
                echo '<div class="col-12 text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                        <p>No se encontraron adornos.</p>
                    </div>';
            } else {
                while ($row = $res->fetch_assoc()):
                    $item_id = (int)$row['id'];
                    $code = htmlspecialchars($row['code'] ?? $row['id']);
                    $desc = $row['description'] ?? '';
                    $avail = (int)$row['available_quantity'];
                    $total = (int)$row['total_quantity'];
                    $image = $row['image'] ?? '';
                    $image = $image ? basename(trim($image)) : '';
                    $celebration_id = (int)($row['celebration_id'] ?? 0);

                    $stockClass = ($avail <= 0) ? 'out-of-stock' : '';

                    $cname = '';
                    if($celebration_id){
                        $cname = $conn->query("SELECT name FROM celebrations WHERE id = " . $celebration_id)->fetch_assoc()['name'] ?? '';
                    }

                    $dept_list = $reserved_map[$item_id] ?? [];
            ?>
            <div class="col-sm-6 col-lg-4 col-xl-3" id="item-col-<?= $item_id ?>">
                <div class="card item-card <?= $stockClass ?>" id="item-card-<?= $item_id ?>">

                    <div class="card-img-wrapper" id="imgwrap-<?= $item_id ?>">
                        <?php if(!empty($image)): ?>
                        <img
                            id="item-img-<?= $item_id ?>"
                            src="uploads/<?= htmlspecialchars($image) ?>"
                            alt="Adorno"
                            class="item-img"
                            data-fullsrc="uploads/<?= htmlspecialchars($image) ?>"
                            style="cursor: zoom-in;"
                        >
                        <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light text-secondary" id="noimg-<?= $item_id ?>">
                            <i class="fas fa-image fa-3x opacity-25"></i>
                        </div>
                        <?php endif; ?>

                        <!-- Agotado (SIEMPRE existe, solo se muestra/oculta) -->
                        <div class="status-overlay" id="status-overlay-<?= $item_id ?>" style="<?= ($avail <= 0) ? '' : 'display:none;' ?>">
                            <span class="badge bg-danger shadow-sm"><i class="fas fa-ban me-1"></i>Agotado</span>
                        </div>

                        <!-- Celebración badge (SIEMPRE existe) -->
                        <div class="badge-overlay" id="celebration-badge-<?= $item_id ?>" style="<?= $cname ? '' : 'display:none;' ?>">
                            <?= htmlspecialchars($cname) ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="item-code" id="item-code-<?= $item_id ?>">Código: <?= $code ?></h5>
                        </div>

                        <p class="item-desc text-truncate-2" id="item-desc-<?= $item_id ?>">
                            <?= !empty($desc) ? nl2br(htmlspecialchars($desc)) : '<em class="text-muted small">Sin descripción</em>' ?>
                        </p>

                        <div class="stats-container">
                            <div class="stat-item border-end w-50">
                                <span class="text-muted small">Total</span>
                                <span class="stat-value" id="total-<?= $item_id ?>"><?= $total ?></span>
                            </div>
                            <div class="stat-item w-50">
                                <span class="text-muted small">Disponibles</span>
                                <span id="avail-<?= $item_id ?>"
                                    class="stat-value <?= $avail > 0 ? 'text-success' : 'text-danger' ?>"><?= $avail ?></span>
                            </div>
                        </div>

                        <!-- Apartado por (SIEMPRE existe, solo se muestra/oculta) -->
                        <div class="reserved-info" id="reserved-info-<?= $item_id ?>" style="<?= empty($dept_list) ? 'display:none;' : '' ?>">
                            <i class="fas fa-lock me-1"></i> <strong>Apartado por:</strong><br>
                            <span id="reserved-text-<?= $item_id ?>">
                            <?php
                                if (!empty($dept_list)){
                                    $parts = [];
                                    foreach($dept_list as $dinfo){
                                        $parts[] = htmlspecialchars($dinfo['dept_name']) . ' (' . (int)$dinfo['qty'] . ')';
                                    }
                                    echo implode(', ', $parts);
                                }
                            ?>
                            </span>
                        </div>

                        <div class="action-buttons">
                            <!-- Botón reservar (siempre existe) -->
                            <button
                                id="reserve-btn-<?= $item_id ?>"
                                class="btn <?= ($avail > 0) ? 'btn-primary btn-reserve' : 'btn-secondary' ?> flex-grow-1 rounded-3"
                                <?= ($avail > 0) ? 'data-bs-toggle="modal" data-bs-target="#reserveModal"' : 'disabled' ?>
                                data-itemid="<?= $item_id ?>"
                                data-code="<?= $code ?>"
                                data-available="<?= $avail ?>"
                            >
                                <?= ($avail > 0) ? '<i class="fas fa-cart-plus me-1"></i> Reservar' : 'No disponible' ?>
                            </button>

                            <?php if(current_user()['role'] === 'admin'): ?>
                            <div class="dropdown">
                                <button class="btn btn-light border" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fas fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button
                                            class="dropdown-item js-open-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editItemModal"
                                            data-id="<?= $item_id ?>"
                                            data-code="<?= $code ?>"
                                            data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                                            data-total="<?= $total ?>"
                                            data-available="<?= $avail ?>"
                                            data-image="<?= htmlspecialchars($image, ENT_QUOTES) ?>"
                                            data-celebration="<?= $celebration_id ?>"
                                        >
                                            <i class="fas fa-pen me-2 text-warning"></i> Editar
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button
                                            class="dropdown-item text-danger js-open-delete"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteConfirmModal"
                                            data-id="<?= $item_id ?>"
                                            data-code="<?= $code ?>"
                                        >
                                            <i class="fas fa-trash me-2"></i> Eliminar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php
                endwhile;
            }
            ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $sel ? '&celebration='.$sel : '' ?>"
                        aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $sel ? '&celebration='.$sel : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $sel ? '&celebration='.$sel : '' ?>"
                        aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>

    <!-- ====== MODALES ====== -->

    <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form method="POST" action="add_item.php" enctype="multipart/form-data" class="modal-content"
                id="addItemForm">

                <div class="modal-header-custom">
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                    <div class="modal-icon-circle">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="modal-title-custom">Nuevo Adorno</h3>
                    <p class="modal-subtitle">Ingresa los detalles.</p>
                </div>

                <div class="modal-body-custom">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-floating mb-2">
                                <input name="code" id="add_code" class="form-control" placeholder="Código" required>
                                <label>Código</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating mb-2">
                                <input type="number" name="total_quantity" id="add_total" class="form-control" min="1"
                                    value="1" placeholder="Total" required>
                                <label>Total</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-2">
                                <select name="celebration_id" id="add_celebration" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php
                                    $cs = $conn->query("SELECT id, name FROM celebrations ORDER BY name");
                                    while($c = $cs->fetch_assoc()){
                                        echo "<option value=\"{$c['id']}\">".htmlspecialchars($c['name'])."</option>";
                                    }
                                    ?>
                                </select>
                                <label>Celebración</label>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <label class="form-label text-muted small fw-bold">Imagen</label>
                            <input type="file" name="image" id="add_image" accept="image/*"
                                class="form-control form-control-sm">
                            <div class="text-center mt-2">
                                <img id="add_preview" src="#" alt="Vista previa"
                                    style="display:none; max-height:100px; margin:0 auto; border-radius:8px;">
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea name="description" id="add_description" class="form-control"
                                    placeholder="Descripción" style="height: 80px"></textarea>
                                <label>Descripción</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-modal">
                        Guardar Adorno <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <!-- action original como fallback; AJAX intercepta el submit -->
            <form method="POST" action="item_action.php?action=edit" enctype="multipart/form-data" class="modal-content"
                id="editItemForm">

                <div class="modal-header-custom">
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                    <div class="modal-icon-circle warning">
                        <i class="fas fa-pen-nib"></i>
                    </div>
                    <h3 class="modal-title-custom">Editar Adorno</h3>
                    <p class="modal-subtitle">Modifica la información.</p>
                </div>

                <div class="modal-body-custom">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-floating mb-2">
                                <input name="code" id="edit_code" class="form-control edit-input" placeholder="Código"
                                    required>
                                <label>Código</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating mb-2">
                                <input type="number" name="total_quantity" id="edit_total"
                                    class="form-control edit-input" min="1" placeholder="Total" required>
                                <label>Total</label>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <small class="text-muted d-block bg-light p-2 rounded border border-warning-subtle"
                                id="edit_reserved_text" style="font-size: 0.75rem;"></small>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-2">
                                <select name="celebration_id" id="edit_celebration" class="form-select edit-input"
                                    required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php
                                    $cs2 = $conn->query("SELECT id, name FROM celebrations ORDER BY name");
                                    while($c2 = $cs2->fetch_assoc()){
                                        echo "<option value=\"{$c2['id']}\">".htmlspecialchars($c2['name'])."</option>";
                                    }
                                    ?>
                                </select>
                                <label>Celebración</label>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <label class="form-label text-muted small fw-bold">Imagen Actual / Nueva</label>
                            <div class="d-flex gap-2 align-items-center bg-light p-2 rounded border">
                                <div style="width: 50px; height: 50px; flex-shrink:0;">
                                    <img id="edit_preview" src="#"
                                        style="width:100%; height:100%; object-fit:cover; display:none; border-radius:6px;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="hidden" name="existing_image" id="edit_existing_image">
                                    <input type="file" name="image" id="edit_image" accept="image/*"
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea name="description" id="edit_description" class="form-control edit-input"
                                    placeholder="Desc" style="height: 80px"></textarea>
                                <label>Descripción</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-modal warning">
                        Guardar Cambios <i class="fas fa-check ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <!-- action original como fallback; AJAX intercepta el submit -->
            <form method="POST" action="item_action.php?action=delete" class="modal-content" id="deleteItemForm">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4 px-4">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">¿Eliminar Adorno?</h4>
                    <p class="text-muted mb-4 small">
                        Vas a eliminar el artículo código <strong id="delete_item_code" class="text-dark"></strong>.<br>
                        Esta acción no se puede deshacer.
                    </p>
                    <input type="hidden" name="id" id="delete_item_id">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 fw-medium"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">Sí, eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="reserveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form id="reserveForm" method="post" action="reserve.php" class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-shopping-bag me-2"></i>Reservar Adorno</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="modal_item_id">

                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-primary" id="modal_display_code"></h3>
                        <p class="text-muted" id="modal_available_text"></p>
                    </div>

                    <div class="mb-3 d-none">
                        <input type="text" id="modal_item_code" class="form-control" readonly>
                    </div>

                    <div class="form-floating mb-3">
                        <select name="dept_id" id="modal_dept_select" class="form-select" required>
                            <option value="">-- Seleccionar Departamento --</option>
                            <?php
                            $deps = $conn->query("SELECT id, name FROM departments ORDER BY name");
                            while($d = $deps->fetch_assoc()){
                                echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['name'])."</option>";
                            }
                            ?>
                        </select>
                        <label>Departamento</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" name="quantity" id="modal_qty" class="form-control" value="1" min="1"
                            required>
                        <label>Cantidad a reservar</label>
                    </div>

                    <div class="form-floating">
                        <input type="text" name="notes" class="form-control" placeholder="Notas">
                        <label>Notas (opcional)</label>
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para ver imagen -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="background: #e6ffe6;">
            <!-- Header mejorado -->
            <div class="modal-header border-0 px-4 py-3" style="background: #2d5a3d;">
                <h5 class="modal-title text-white mb-0">
                    <i class="fas fa-image me-2" style="color: #4caf50;"></i>
                    Vista previa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <!-- Body con imagen -->
            <div class="modal-body p-4">
                <img id="modalImage" 
                     src="" 
                     alt="Vista previa" 
                     class="img-fluid w-100 h-100 rounded-4" 
                     style="object-fit: contain; max-height: 70vh;">
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ========= Helpers =========
    function nl2brSafe(text){
        if (text == null) return '';
        return String(text).replace(/\n/g, '<br>');
    }

    function escapeHtml(str){
        return String(str)
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    }

    // Imagen preview (add)
    const addImage = document.getElementById('add_image');
    const addPreview = document.getElementById('add_preview');
    if (addImage) {
        addImage.addEventListener('change', function() {
            const f = this.files[0];
            if (!f || !f.type.startsWith('image/')) {
                addPreview.style.display = 'none';
                addPreview.src = '#';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                addPreview.src = e.target.result;
                addPreview.style.display = 'block';
            };
            reader.readAsDataURL(f);
        });
    }

    // Uppercase code (add)
    const addCode = document.getElementById('add_code');
    if (addCode) {
        addCode.addEventListener('input', () => addCode.value = addCode.value.toUpperCase().replace(/\s+/g, ''));
    }

    // Validación antes de enviar (add)
    document.getElementById('addItemForm')?.addEventListener('submit', function(e) {
        const celebration = document.getElementById('add_celebration');
        if (celebration && celebration.value === '') {
            e.preventDefault();
            alert('Selecciona una celebración.');
            return false;
        }
        return true;
    });

    // Preview Editar (solo visual)
    const editImageInput = document.getElementById('edit_image');
    const editPreview = document.getElementById('edit_preview');
    if (editImageInput) {
        editImageInput.addEventListener('change', function() {
            const f = this.files[0];
            if (!f || !f.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                editPreview.src = e.target.result;
                editPreview.style.display = 'block';
            };
            reader.readAsDataURL(f);
        });
    }

    // ========= Modal Edit: cargar datos =========
    const editModalEl = document.getElementById('editItemModal');
    editModalEl?.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const code = button.getAttribute('data-code');
        const description = button.getAttribute('data-description') || '';
        const total = button.getAttribute('data-total') || '1';
        const available = button.getAttribute('data-available') || '0';
        const image = button.getAttribute('data-image') || '';
        const celebration = button.getAttribute('data-celebration') || '';

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_code').value = code;
        document.getElementById('edit_total').value = total;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_existing_image').value = image;
        document.getElementById('edit_celebration').value = celebration;

        if (image) {
            editPreview.src = 'uploads/' + image;
            editPreview.style.display = 'block';
        } else {
            editPreview.src = '#';
            editPreview.style.display = 'none';
        }

        let reserved = parseInt(total, 10) - parseInt(available, 10);
        if (reserved < 0) reserved = 0;
        document.getElementById('edit_reserved_text').innerHTML =
            '<i class="fas fa-info-circle text-warning"></i> Reservados: <strong>' + reserved + '</strong>';
    });

    // ========= Modal Delete: cargar datos =========
    const deleteModalEl = document.getElementById('deleteConfirmModal');
    deleteModalEl?.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const code = button.getAttribute('data-code');

        document.getElementById('delete_item_id').value = id;
        document.getElementById('delete_item_code').textContent = '«' + code + '»';
    });

    // ========= Modal Reservar: set data =========
    const reserveModalEl = document.getElementById('reserveModal');
    reserveModalEl?.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const itemId = button.getAttribute('data-itemid');
        const code = button.getAttribute('data-code');
        const available = parseInt(button.getAttribute('data-available') || '0', 10);

        document.getElementById('modal_item_id').value = itemId;
        document.getElementById('modal_item_code').value = code;
        document.getElementById('modal_display_code').textContent = 'Código: ' + code;

        document.getElementById('modal_qty').value = Math.min(1, available);
        document.getElementById('modal_qty').max = Math.max(1, available);

        const availText = document.getElementById('modal_available_text');
        availText.textContent = 'Stock disponible: ' + available;
        if (available < 3) availText.className = 'text-danger fw-bold';
        else availText.className = 'text-success fw-bold';

        const deptSelect = document.getElementById('modal_dept_select');
        <?php if(current_user()['role'] === 'usuario' && !empty(current_user()['department_id'])): ?>
        deptSelect.value = "<?= (int)current_user()['department_id'] ?>";
        deptSelect.disabled = true;

        let existingHidden = document.getElementById('modal_dept_hidden');
        if (!existingHidden) {
            const h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'dept_id';
            h.id = 'modal_dept_hidden';
            h.value = "<?= (int)current_user()['department_id'] ?>";
            document.getElementById('reserveForm').appendChild(h);
        } else {
            existingHidden.value = "<?= (int)current_user()['department_id'] ?>";
        }
        <?php else: ?>
        if (deptSelect.disabled) deptSelect.disabled = false;
        const existingHidden = document.getElementById('modal_dept_hidden');
        if (existingHidden) existingHidden.remove();
        <?php endif; ?>
    });

    // ========= RESERVAR SIN SALIR (AJAX) =========
    document.getElementById('reserveForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const qtyInput = document.getElementById('modal_qty');

        const qty = parseInt(qtyInput.value || '0', 10);
        const max = parseInt(qtyInput.max || '0', 10);

        if (qty < 1 || qty > max) {
            alert('Cantidad inválida. Debe ser entre 1 y ' + max);
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const oldHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Reservando...';

        try {
            const fd = new FormData(form);

            const resp = await fetch('ajax/reserve_ajax.php', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await resp.json();

            if (!json.ok) {
                alert(json.message || 'No se pudo reservar.');
                return;
            }

            // Cerrar modal
            const modalInstance = bootstrap.Modal.getInstance(reserveModalEl);
            if (modalInstance) modalInstance.hide();

            // Actualizar UI del item
            const data = json.data;
            const itemId = data.item_id;
            const avail = data.available_quantity;

            // Disponibles
            const availSpan = document.getElementById('avail-' + itemId);
            if (availSpan) {
                availSpan.textContent = String(avail);
                availSpan.classList.remove('text-success','text-danger');
                availSpan.classList.add(avail > 0 ? 'text-success' : 'text-danger');
            }

            // Botón reservar
            const reserveBtn = document.getElementById('reserve-btn-' + itemId);
            if (reserveBtn) {
                reserveBtn.setAttribute('data-available', String(avail));

                if (avail <= 0) {
                    reserveBtn.classList.remove('btn-primary','btn-reserve');
                    reserveBtn.classList.add('btn-secondary');
                    reserveBtn.disabled = true;
                    reserveBtn.removeAttribute('data-bs-toggle');
                    reserveBtn.removeAttribute('data-bs-target');
                    reserveBtn.innerHTML = 'No disponible';
                } else {
                    reserveBtn.disabled = false;
                    reserveBtn.setAttribute('data-bs-toggle','modal');
                    reserveBtn.setAttribute('data-bs-target','#reserveModal');
                }
            }

            // Agotado badge + clase out-of-stock
            const card = document.getElementById('item-card-' + itemId);
            const overlay = document.getElementById('status-overlay-' + itemId);
            if (avail <= 0) {
                if (card) card.classList.add('out-of-stock');
                if (overlay) overlay.style.display = '';
            } else {
                if (overlay) overlay.style.display = 'none';
            }

            // “Apartado por”
            const reservedInfo = document.getElementById('reserved-info-' + itemId);
            const reservedText = document.getElementById('reserved-text-' + itemId);
            if (reservedInfo && reservedText) {
                const parts = (data.reserved_by || []).map(x => `${x.dept_name} (${x.qty})`);
                reservedText.textContent = parts.join(', ');
                reservedInfo.style.display = parts.length ? '' : 'none';
            }

            // Limpiar modal
            form.querySelector('input[name="notes"]').value = '';
            qtyInput.value = 1;

        } catch (err) {
            console.error(err);
            alert('Error de red o del servidor al reservar.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = oldHtml;
        }
    });

    // ========= EDITAR SIN SALIR (AJAX) =========
    document.getElementById('editItemForm')?.addEventListener('submit', async function(e){
        e.preventDefault();

        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const oldHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Guardando...';

        try{
            const fd = new FormData(form);

            // IMPORTANTE: este endpoint lo crearás en el paso 3
            const resp = await fetch('ajax/item_action_ajax.php?action=edit', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await resp.json();

            if(!json.ok){
                alert(json.message || 'No se pudo editar.');
                return;
            }

            const data = json.data;
            const itemId = data.item_id;

            // Cerrar modal
            const inst = bootstrap.Modal.getInstance(editModalEl);
            if(inst) inst.hide();

            // Actualizar tarjeta: código / desc
            const codeEl = document.getElementById('item-code-' + itemId);
            if(codeEl) codeEl.textContent = 'Código: ' + (data.code || '');

            const descEl = document.getElementById('item-desc-' + itemId);
            if(descEl){
                if((data.description || '').trim() === ''){
                    descEl.innerHTML = '<em class="text-muted small">Sin descripción</em>';
                } else {
                    descEl.innerHTML = nl2brSafe(escapeHtml(data.description));
                }
            }

            // Total / disponibles
            const totalEl = document.getElementById('total-' + itemId);
            if(totalEl) totalEl.textContent = String(data.total_quantity ?? '');

            const availEl = document.getElementById('avail-' + itemId);
            const avail = parseInt(data.available_quantity ?? 0, 10);
            if(availEl){
                availEl.textContent = String(avail);
                availEl.classList.remove('text-success','text-danger');
                availEl.classList.add(avail > 0 ? 'text-success' : 'text-danger');
            }

            // Imagen (si viene una nueva ruta/nombre)
            if(data.image){
                const img = document.getElementById('item-img-' + itemId);
                const wrap = document.getElementById('imgwrap-' + itemId);
                const noimg = document.getElementById('noimg-' + itemId);

                const src = 'uploads/' + data.image;

                if(img){
                    img.src = src;
                    img.setAttribute('data-fullsrc', src);
                    img.style.cursor = 'zoom-in';
                } else if(wrap){
                    // si antes no había imagen, crearla
                    if(noimg) noimg.remove();
                    const newImg = document.createElement('img');
                    newImg.id = 'item-img-' + itemId;
                    newImg.src = src;
                    newImg.alt = 'Adorno';
                    newImg.className = 'item-img';
                    newImg.setAttribute('data-fullsrc', src);
                    newImg.style.cursor = 'zoom-in';
                    wrap.prepend(newImg);
                }
            }

            // Celebración badge
            const badge = document.getElementById('celebration-badge-' + itemId);
            if(badge){
                const cname = (data.celebration_name || '').trim();
                if(cname){
                    badge.textContent = cname;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }

            // Botón reservar + agotado
            const reserveBtn = document.getElementById('reserve-btn-' + itemId);
            const overlay = document.getElementById('status-overlay-' + itemId);
            const card = document.getElementById('item-card-' + itemId);

            if(reserveBtn){
                reserveBtn.setAttribute('data-available', String(avail));
                reserveBtn.setAttribute('data-code', data.code || reserveBtn.getAttribute('data-code') || '');

                if(avail <= 0){
                    reserveBtn.classList.remove('btn-primary','btn-reserve');
                    reserveBtn.classList.add('btn-secondary');
                    reserveBtn.disabled = true;
                    reserveBtn.removeAttribute('data-bs-toggle');
                    reserveBtn.removeAttribute('data-bs-target');
                    reserveBtn.innerHTML = 'No disponible';
                    if(card) card.classList.add('out-of-stock');
                    if(overlay) overlay.style.display = '';
                } else {
                    reserveBtn.disabled = false;
                    reserveBtn.classList.remove('btn-secondary');
                    reserveBtn.classList.add('btn-primary','btn-reserve');
                    reserveBtn.setAttribute('data-bs-toggle','modal');
                    reserveBtn.setAttribute('data-bs-target','#reserveModal');
                    reserveBtn.innerHTML = '<i class="fas fa-cart-plus me-1"></i> Reservar';
                    if(overlay) overlay.style.display = 'none';
                    if(card) card.classList.remove('out-of-stock'); // opcional
                }
            }

            // Actualizar dataset del botón "Editar" (para que la siguiente vez cargue lo nuevo)
            const editBtn = document.querySelector(`.js-open-edit[data-id="${itemId}"]`);
            if(editBtn){
                editBtn.setAttribute('data-code', data.code || '');
                editBtn.setAttribute('data-description', data.description || '');
                editBtn.setAttribute('data-total', String(data.total_quantity ?? ''));
                editBtn.setAttribute('data-available', String(data.available_quantity ?? ''));
                editBtn.setAttribute('data-image', data.image || editBtn.getAttribute('data-image') || '');
                editBtn.setAttribute('data-celebration', String(data.celebration_id ?? ''));
            }

        }catch(err){
            console.error(err);
            alert('Error de red o del servidor al editar.');
        }finally{
            submitBtn.disabled = false;
            submitBtn.innerHTML = oldHtml;
        }
    });

    // ========= ELIMINAR SIN SALIR (AJAX) =========
    document.getElementById('deleteItemForm')?.addEventListener('submit', async function(e){
        e.preventDefault();

        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const oldHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Eliminando...';

        try{
            const fd = new FormData(form);

            // IMPORTANTE: este endpoint lo crearás en el paso 3
            const resp = await fetch('ajax/item_action_ajax.php?action=delete', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await resp.json();

            if(!json.ok){
                alert(json.message || 'No se pudo eliminar.');
                return;
            }

            const itemId = json.data?.item_id;

            // Cerrar modal
            const inst = bootstrap.Modal.getInstance(deleteModalEl);
            if(inst) inst.hide();

            // Quitar del DOM
            const col = document.getElementById('item-col-' + itemId);
            if(col) col.remove();

        }catch(err){
            console.error(err);
            alert('Error de red o del servidor al eliminar.');
        }finally{
            submitBtn.disabled = false;
            submitBtn.innerHTML = oldHtml;
        }
    });

    // ========= Modal Imagen (solo abrir/cerrar) =========
    (function(){
        const imageModalEl = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        document.addEventListener('click', function(e){
            const img = e.target.closest('.item-img');
            if(!img) return;

            const src = img.getAttribute('data-fullsrc') || img.getAttribute('src');
            if(!src) return;

            modalImage.src = src;
            bootstrap.Modal.getOrCreateInstance(imageModalEl).show();
        });

        imageModalEl?.addEventListener('hidden.bs.modal', () => {
            modalImage.src = '';
        });
    })();
    </script>

    <?php include("footer.php"); ?>
</body>
</html>
