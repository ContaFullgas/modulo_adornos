<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

// --- PAGINACIÓN ---
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

// --- FILTROS Y BÚSQUEDA ---
$whereConditions = [];
$user_dept_id = (int)(current_user()['department_id'] ?? 0);

// 1. Filtro de Rol
if (current_user()['role'] !== 'admin') {
    if ($user_dept_id > 0) {
        $whereConditions[] = "r.dept_id = $user_dept_id";
    } else {
        $whereConditions[] = "1 = 0";
    }
}

// 2. Filtro de Estado
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
if ($statusFilter !== 'all') {
    if ($statusFilter === 'reservado') {
        $whereConditions[] = "LOWER(r.status) = 'reservado'";
    } elseif ($statusFilter === 'en_proceso') {
        $whereConditions[] = "(LOWER(r.status) = 'en proceso' OR LOWER(r.status) = 'en_proceso')";
    } elseif ($statusFilter === 'finalizado') {
        $whereConditions[] = "(LOWER(r.status) = 'finalizado' OR LOWER(r.status) = 'devuelto')";
    }
}

// 3. Buscador
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!empty($searchQuery)) {
    $s = $conn->real_escape_string($searchQuery);
    $whereConditions[] = "(i.code LIKE '%$s%' OR u.username LIKE '%$s%' OR d.name LIKE '%$s%')";
}

// Construir WHERE
$whereClause = "";
if (count($whereConditions) > 0) {
    $whereClause = "WHERE " . implode(' AND ', $whereConditions);
}

// TOTALES
$count_sql = "SELECT COUNT(*) as total
              FROM reservations r
              JOIN items i ON i.id = r.item_id
              LEFT JOIN users u ON u.id = r.user_id
              JOIN departments d ON d.id = r.dept_id
              $whereClause";
$count_res = $conn->query($count_sql);
$total_items = $count_res ? (int)$count_res->fetch_assoc()['total'] : 0;
$total_pages = (int)ceil($total_items / $items_per_page);

// CONSULTA PRINCIPAL
$sql = "
    SELECT r.*, 
           d.name AS dept_name,
           i.code AS item_code, 
           i.description AS item_description, 
           i.image AS item_image,
           u.username AS user_name,
           ret.return_condition,
           ret.condition_notes
    FROM reservations r
    JOIN departments d ON d.id = r.dept_id
    JOIN items i ON i.id = r.item_id
    LEFT JOIN users u ON u.id = r.user_id
    LEFT JOIN returns ret ON ret.reservation_id = r.id
    $whereClause
    ORDER BY r.reserved_at DESC
    LIMIT $offset, $items_per_page
";

$result = $conn->query($sql);
$isAdminGlobal = (current_user()['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
    body {
        background-color: #f9fafb;
        font-family: 'Inter', sans-serif;
        padding-top: 100px;
    }

    /* Barra de Filtros */
    .filter-bar {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .search-group {
        flex-grow: 1;
        max-width: 400px;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .search-input {
        padding-left: 36px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .search-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* Tabla */
    .table-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .clean-table {
        width: 100%;
        border-collapse: collapse;
    }

    .clean-table thead th {
        background-color: #ffffff;
        color: #9ca3af;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 1.25rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid #f3f4f6;
    }

    .clean-table tbody td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f9fafb;
        color: #374151;
        font-size: 0.9rem;
    }

    .clean-table tbody tr:hover {
        background-color: #fcfcfc;
    }

    .img-thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #f3f4f6;
        cursor: zoom-in;
    }

    .code-badge {
        display: inline-block;
        background-color: #eff6ff;
        color: #3b82f6;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 4px 10px;
        border-radius: 6px;
        margin-bottom: 4px;
    }

    .user-avatar-circle {
        width: 32px;
        height: 32px;
        background-color: #6366f1;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        margin-right: 10px;
    }

    /* Estados */
    .status-pill {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .st-apartado {
        background-color: #ecfdf5;
        color: #10b981;
    }

    .st-apartado::before {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        background: #10b981;
        border-radius: 50%;
    }

    .st-gris {
        background-color: #f3f4f6;
        color: #6b7280;
    }

    .st-gris::before {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        background: #9ca3af;
        border-radius: 50%;
    }

    .st-proceso {
        background-color: #fffbeb;
        color: #d97706;
    }

    .st-proceso::before {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        background: #f59e0b;
        border-radius: 50%;
    }

    /* Botones y Selects */
    .admin-select {
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.4rem 2rem 0.4rem 0.8rem;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        width: 140px;
        transition: all 0.2s;
    }

    .sel-red {
        color: #dc2626;
        background-color: #fef2f2;
        border-color: #fecaca;
    }

    .sel-yellow {
        color: #d97706;
        background-color: #fffbeb;
        border-color: #fde68a;
    }

    .sel-green {
        color: #059669;
        background-color: #ecfdf5;
        border-color: #a7f3d0;
    }

    .btn-outline-custom {
        border: 1px solid #fed7aa;
        background: white;
        color: #ea580c;
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-outline-custom:hover {
        background-color: #fff7ed;
        border-color: #f97316;
    }

    /* Modales (generales) */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid #f3f4f6;
        padding: 1.5rem;
    }

    .modal-body {
        padding: 2rem;
        text-align: center;
    }

    .status-icon-large {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .btn-confirm-modal {
        width: 100%;
        padding: 0.8rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        color: white;
    }

    /* Estilos adicionales para los campos de condición */
    #conditionFields .form-select,
    #conditionFields .form-control {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }

    #conditionFields .form-select:focus,
    #conditionFields .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    #conditionFields .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    #conditionNotesContainer textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* Paginación */
    .pagination .page-link {
        color: #374151;
        border: none;
        margin: 0 2px;
        border-radius: 6px;
    }

    .pagination .page-item.active .page-link {
        background-color: #f3f4f6;
        color: #111827;
        font-weight: bold;
    }

    /* ===== Modal Imagen (MISMO ESTILO QUE items.php) ===== */
    #imageModal .modal-content {
        background: #0b1220;
        border: 0;
    }

    #imageModal .modal-header {
        border: 0;
    }

    #imageModal .modal-body {
        padding: 0;
    }

    #imageModal .img-stage {
        position: relative;
        width: 100%;
        height: min(80vh, 760px);
        overflow: hidden;
        background: #0b1220;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #imageModal #modalImage {
        width: 100%;
        height: 100%;
        object-fit: contain;
        user-select: none;
        -webkit-user-drag: none;
        cursor: default;
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

    <div class="container py-5">

        <div class="filter-bar">
            <form method="get" class="d-flex w-100 gap-3 align-items-center flex-wrap">
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" class="form-control search-input"
                        placeholder="Buscar por código, usuario..." value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small fw-bold text-uppercase m-0">Estado:</label>
                    <select name="status"
                        class="form-select form-select-sm border-secondary-subtle fw-medium text-secondary"
                        style="width: 160px;" onchange="this.form.submit()">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Todos</option>
                        <option value="reservado" <?= $statusFilter === 'reservado' ? 'selected' : '' ?>>🔴 No Recibido
                        </option>
                        <option value="en_proceso" <?= $statusFilter === 'en_proceso' ? 'selected' : '' ?>>🟡 Pendiente
                        </option>
                        <option value="finalizado" <?= $statusFilter === 'finalizado' ? 'selected' : '' ?>>🟢 Recibido
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark btn-sm rounded-pill px-3">Aplicar</button>
                <?php if(!empty($searchQuery) || $statusFilter !== 'all'): ?>
                <a href="reservations.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-auto"><i
                        class="fas fa-times me-1"></i> Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="clean-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-building me-2"></i>Departamento</th>
                            <th>Artículo</th>
                            <th>Detalle</th>
                            <th>Cant.</th>
                            <th>Solicitado Por</th>
                            <th>Estado Actual</th>

                            <?php if($isAdminGlobal): ?>
                            <th style="width: 250px;">Observaciones</th>
                            <?php endif; ?>

                            <th><i class="far fa-calendar me-2"></i>Fecha</th>
                            <th class="text-end">Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                $rid = (int)$row['id'];
                                $dept_name = htmlspecialchars($row['dept_name'] ?? '');
                                $item_code = htmlspecialchars($row['item_code'] ?? '');
                                $item_desc = trim($row['item_description'] ?? '');
                                $item_image = $row['item_image'] ?? '';
                                $quantity = (int)$row['quantity'];
                                $user_name = htmlspecialchars($row['user_name'] ?? 'Usuario');
                                $initial = strtoupper(substr($user_name, 0, 1));

                                // Normalizar status
                                $status_raw = strtolower(trim((string)($row['status'] ?? 'reservado')));
                                $status_norm = 'reservado';
                                if ($status_raw === 'reservado') $status_norm = 'reservado';
                                elseif ($status_raw === 'en proceso' || $status_raw === 'en_proceso') $status_norm = 'en_proceso';
                                elseif ($status_raw === 'finalizado' || $status_raw === 'devuelto') $status_norm = 'finalizado';

                                $isAdmin = $isAdminGlobal;
                                $isMyDept = ($user_dept_id > 0 && (int)$row['dept_id'] === $user_dept_id);

                                $selectClass = 'text-dark bg-light';
                                if ($status_norm === 'reservado') $selectClass = 'sel-red';
                                elseif ($status_norm === 'en_proceso') $selectClass = 'sel-yellow';
                                elseif ($status_norm === 'finalizado') $selectClass = 'sel-green';

                                $dateStr = (new DateTime($row['reserved_at']))->format('d M, Y');
                                $timeStr = (new DateTime($row['reserved_at']))->format('h:i A');

                                // Imagen segura para abrir modal
                                $imgFile = trim((string)$item_image);
                                $imgSrc = '';
                                if(!empty($imgFile)){
                                    $imgSrc = 'uploads/' . rawurlencode(basename($imgFile));
                                }
                                // --- CONDICIÓN DE DEVOLUCIÓN ---
$condicion = $row['return_condition'] ?? '';
$nota_db   = trim($row['condition_notes'] ?? '');
$nota_final = '';

if ($nota_db !== '') {
    $nota_final = $nota_db;
} else {
    if ($condicion === 'buen_estado') {
        $nota_final = 'Artículo devuelto en buen estado.';
    } elseif ($condicion === 'roto') {
        $nota_final = 'Artículo devuelto dañado.';
    } elseif ($condicion === 'incompleto') {
        $nota_final = 'Artículo devuelto incompleto.';
    } elseif ($status_norm === 'finalizado') {
        // Finalizado pero sin registro de retorno (caso raro)
        $nota_final = 'Devolución finalizada sin observaciones.';
    } else {
        // Aún no devuelto
        $nota_final = '—';
    }
}

                        ?>
                        <tr id="res-row-<?= $rid ?>" data-isadmin="<?= $isAdmin ? '1' : '0' ?>"
                            data-ismydept="<?= $isMyDept ? '1' : '0' ?>">
                            <td class="fw-medium"><?= $dept_name ?></td>

                            <td>
                                <?php if(!empty($imgSrc)): ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" class="img-thumb js-open-img" alt="img"
                                    role="button" tabindex="0" data-fullsrc="<?= htmlspecialchars($imgSrc) ?>"
                                    data-title="<?= htmlspecialchars($item_code ?: 'Vista previa') ?>">
                                <?php else: ?>
                                <div
                                    class="img-thumb d-flex align-items-center justify-content-center bg-light text-muted">
                                    <i class="fas fa-image"></i>
                                </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="code-badge"><?= $item_code ?></span>
                                    <?php if(!empty($item_desc)): ?>
                                    <span class="text-muted small text-truncate" style="max-width: 150px;"
                                        title="<?= htmlspecialchars($item_desc) ?>"><?= htmlspecialchars($item_desc) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted small fst-italic opacity-50">Sin descripción</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="fw-bold">x<?= $quantity ?></td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-circle"><?= $initial ?></div>
                                    <span class="fw-medium small"><?= $user_name ?></span>
                                </div>
                            </td>

                            <td id="res-statuscell-<?= $rid ?>">
                                <?php if($status_norm === 'reservado'): ?>
                                <span class="status-pill st-apartado">Apartado</span>
                                <?php elseif($status_norm === 'en_proceso'): ?>
                                <span class="status-pill st-proceso">En Proceso</span>
                                <?php else: ?>
                                <span class="status-pill st-gris">Devuelto</span>
                                <?php endif; ?>
                            </td>

                            <?php if($isAdmin): ?>
                            <td>
                                <?php 
                                        // Usamos los valores tal cual vienen del ENUM en BD (minusculas)
                                      $condicion = $row['return_condition'] ?? '';
                                    $notas_bd  = trim($row['condition_notes'] ?? '');
                                     $notas = $notas_bd;

                                // Si NO hay notas escritas, generar texto automático según condición
                                if (empty($notas)) {
                                    if ($condicion === 'buen_estado') {
                                        $notas = 'El artículo fue entregado en buen estado.';
                                    } elseif ($condicion === 'roto') {
                                        $notas = 'El artículo fue entregado dañado.';
                                    } elseif ($condicion === 'incompleto') {
                                        $notas = 'El artículo fue entregado incompleto.';
                                    }
                                }

                                    ?>
                                <?php if(!empty($condicion) || !empty($notas)): ?>
                                <div class="d-flex flex-column gap-1">

                                    <?php if($condicion === 'buen_estado'): ?>
                                    <span class="badge text-bg-success bg-opacity-75" style="width: fit-content;">
                                        <i class="fas fa-check-circle me-1"></i> Buen Estado
                                    </span>
                                    <?php elseif($condicion === 'roto'): ?>
                                    <span class="badge text-bg-danger" style="width: fit-content;">
                                        <i class="fas fa-heart-crack me-1"></i> Roto / Dañado
                                    </span>
                                    <?php elseif($condicion === 'incompleto'): ?>
                                    <span class="badge text-bg-warning text-dark" style="width: fit-content;">
                                        <i class="fas fa-puzzle-piece me-1"></i> Incompleto
                                    </span>
                                    <?php endif; ?>

                                    <?php if(!empty($notas)): ?>
                                    <small class="text-muted fst-italic border-start border-3 ps-2 mt-1"
                                        style="font-size: 0.85rem; display:block; word-wrap: break-word;">
                                        <?= nl2br(htmlspecialchars($notas)) ?>
                                    </small>

                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted small opacity-50">-</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium small text-dark"><?= $dateStr ?></span>
                                    <span class="text-muted small" style="font-size: 0.75rem;"><?= $timeStr ?></span>
                                </div>
                            </td>

                            <td class="text-end" id="res-manage-<?= $rid ?>">
                                <?php if ($isAdmin): ?>
                                <select id="res-select-<?= $rid ?>"
                                    class="form-select form-select-sm admin-select <?= $selectClass ?>"
                                    data-id="<?= $rid ?>" data-original="<?= $status_norm ?>"
                                    onchange="openStatusModal(this)">
                                    <option value="reservado" <?= $status_norm === 'reservado' ? 'selected' : '' ?>>🔴
                                        No Recibido</option>
                                    <option value="en_proceso" <?= $status_norm === 'en_proceso' ? 'selected' : '' ?>>🟡
                                        Pendiente</option>
                                    <option value="finalizado" <?= $status_norm === 'finalizado' ? 'selected' : '' ?>>🟢
                                        Recibido</option>
                                </select>
                                <?php else: ?>
                                <?php if ($status_norm === 'reservado' && $isMyDept): ?>
                                <button class="btn-outline-custom" onclick="openUserReturnModal(<?= $rid ?>)">
                                    <i class="fas fa-undo-alt"></i> Devolver
                                </button>
                                <?php elseif ($status_norm === 'en_proceso'): ?>
                                <span class="text-warning small fst-italic">
                                    <i class="fas fa-clock me-1"></i> Esperando Admin
                                </span>
                                <?php else: ?>
                                <span class="text-muted small">
                                    <i class="fas fa-check-double me-1"></i> Completado
                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="<?= $isAdminGlobal ? '9' : '8' ?>" class="text-center py-5 text-muted">No se
                                encontraron registros.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-4 d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $page - 1 ?>&status=<?= $statusFilter ?>&q=<?= urlencode($searchQuery) ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $i ?>&status=<?= $statusFilter ?>&q=<?= urlencode($searchQuery) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $page + 1 ?>&status=<?= $statusFilter ?>&q=<?= urlencode($searchQuery) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <form method="POST" action="process_return.php" class="modal-content" id="statusForm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Actualizar Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        onclick="cancelStatusChange()"></button>
                </div>
                <div class="modal-body">
                    <div id="statusIconContainer"></div>
                    <p class="mb-4 text-muted">¿Cambiar el estado a <strong id="statusTextDisplay"
                            class="text-dark"></strong>?</p>

                    <input type="hidden" name="reservation_id" id="modal_res_id">
                    <input type="hidden" name="new_status" id="modal_new_status">

                    <div id="conditionFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fas fa-clipboard-check me-2"></i>Estado de los artículos:
                            </label>
                            <select name="return_condition" id="return_condition" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="buen_estado">✅ Buen Estado</option>
                                <option value="roto">🔴 Roto o Dañado</option>
                                <option value="incompleto">⚠️ Incompleto (falta algo)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="conditionNotesContainer" style="display: none;">
                            <label for="condition_notes" class="form-label fw-semibold text-dark">
                                <i class="fas fa-comment-dots me-2"></i>Comentarios:
                            </label>
                            <textarea name="condition_notes" id="condition_notes" class="form-control" rows="3"
                                placeholder="Describe qué está roto o qué falta..."></textarea>
                            <small class="text-muted">Especifica el daño o los artículos faltantes</small>
                        </div>
                    </div>

                    <button type="submit" id="btnConfirmStatus" class="btn-confirm-modal">Confirmar Cambio</button>
                    <button type="button" class="btn btn-link text-muted mt-2 text-decoration-none"
                        data-bs-dismiss="modal" onclick="cancelStatusChange()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="userReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <form method="POST" action="process_return.php" class="modal-content" id="userReturnForm">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-box-open fa-4x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">¿Devolver Artículo?</h4>
                    <p class="text-muted mb-4 small">
                        Se notificará al administrador que estás devolviendo este ítem. El estado cambiará a <strong>"En
                            Proceso"</strong>
                        hasta que sea recibido.
                    </p>

                    <input type="hidden" name="reservation_id" id="user_modal_res_id">
                    <input type="hidden" name="new_status" value="en_proceso">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning fw-bold text-white"
                            style="background-color: #d97706; border:none;">
                            Sí, solicitar devolución
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="background: #e6ffe6;">
                <div class="modal-header border-0 px-4 py-3" style="background: #2d5a3d;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="fas fa-image me-2" style="color: #4caf50;"></i>
                        <span id="imageModalTitle">Vista previa</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <img id="modalImage" src="" alt="Vista previa" class="img-fluid w-100 h-100 rounded-4"
                        style="object-fit: contain; max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let currentSelectElement = null;
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    const userReturnModal = new bootstrap.Modal(document.getElementById('userReturnModal'));

    function selectClassByStatus(status) {
        if (status === 'reservado') return 'sel-red';
        if (status === 'en_proceso') return 'sel-yellow';
        if (status === 'finalizado') return 'sel-green';
        return 'text-dark bg-light';
    }

    function renderStatusPill(status) {
        if (status === 'reservado') {
            return '<span class="status-pill st-apartado">Apartado</span>';
        }
        if (status === 'en_proceso') {
            return '<span class="status-pill st-proceso">En Proceso</span>';
        }
        return '<span class="status-pill st-gris">Devuelto</span>';
    }

    function renderUserManage(status, rid, isMyDept) {
        if (status === 'reservado' && isMyDept) {
            return `<button class="btn-outline-custom" onclick="openUserReturnModal(${rid})">
                        <i class="fas fa-undo-alt"></i> Devolver
                    </button>`;
        }
        if (status === 'en_proceso') {
            return `<span class="text-warning small fst-italic">
                        <i class="fas fa-clock me-1"></i> Esperando Admin
                    </span>`;
        }
        return `<span class="text-muted small">
                    <i class="fas fa-check-double me-1"></i> Completado
                </span>`;
    }

    function applyReservationUI(rid, status) {
        const statusCell = document.getElementById('res-statuscell-' + rid);
        if (statusCell) statusCell.innerHTML = renderStatusPill(status);

        const row = document.getElementById('res-row-' + rid);
        const manageCell = document.getElementById('res-manage-' + rid);
        const isAdmin = row?.getAttribute('data-isadmin') === '1';
        const isMyDept = row?.getAttribute('data-ismydept') === '1';

        if (isAdmin) {
            const sel = document.getElementById('res-select-' + rid);
            if (sel) {
                sel.value = status;
                sel.setAttribute('data-original', status);

                sel.classList.remove('sel-red', 'sel-yellow', 'sel-green', 'text-dark', 'bg-light');
                sel.classList.add(selectClassByStatus(status));
            }
        } else {
            if (manageCell) {
                manageCell.innerHTML = renderUserManage(status, rid, isMyDept);
            }
        }
    }

    // LÓGICA ADMIN (modal)
    function openStatusModal(selectElement) {
        currentSelectElement = selectElement;
        const newStatus = selectElement.value;
        const resId = selectElement.getAttribute('data-id');

        const iconContainer = document.getElementById('statusIconContainer');
        const textDisplay = document.getElementById('statusTextDisplay');
        const btnConfirm = document.getElementById('btnConfirmStatus');
        const conditionFields = document.getElementById('conditionFields');
        const returnCondition = document.getElementById('return_condition');
        const conditionNotes = document.getElementById('condition_notes');

        document.getElementById('modal_res_id').value = resId;
        document.getElementById('modal_new_status').value = newStatus;

        // Resetear campos de condición
        returnCondition.value = '';
        conditionNotes.value = '';
        document.getElementById('conditionNotesContainer').style.display = 'none';

        if (newStatus === 'reservado') {
            iconContainer.innerHTML = '<i class="fas fa-times-circle status-icon-large text-danger"></i>';
            textDisplay.textContent = 'No Recibido';
            btnConfirm.style.backgroundColor = '#dc2626';
            btnConfirm.textContent = 'Marcar como No Recibido';
            conditionFields.style.display = 'none';
            returnCondition.removeAttribute('required');
        } else if (newStatus === 'en_proceso') {
            iconContainer.innerHTML = '<i class="fas fa-clock status-icon-large text-warning"></i>';
            textDisplay.textContent = 'Pendiente';
            btnConfirm.style.backgroundColor = '#d97706';
            btnConfirm.textContent = 'Marcar como Pendiente';
            conditionFields.style.display = 'none';
            returnCondition.removeAttribute('required');
        } else if (newStatus === 'finalizado') {
            iconContainer.innerHTML = '<i class="fas fa-check-circle status-icon-large text-success"></i>';
            textDisplay.textContent = 'Recibido (Finalizado)';
            btnConfirm.style.backgroundColor = '#059669';
            btnConfirm.textContent = 'Confirmar Recepción';
            conditionFields.style.display = 'block';
            returnCondition.setAttribute('required', 'required');
        }

        statusModal.show();
    }
    document.getElementById('return_condition')?.addEventListener('change', function() {
        const notesContainer = document.getElementById('conditionNotesContainer');
        const notesField = document.getElementById('condition_notes');

        if (this.value === 'roto' || this.value === 'incompleto') {
            notesContainer.style.display = 'block';
            notesField.setAttribute('required', 'required');
        } else {
            notesContainer.style.display = 'none';
            notesField.removeAttribute('required');
            notesField.value = '';
        }
    });

    function cancelStatusChange() {
        if (currentSelectElement) {
            currentSelectElement.value = currentSelectElement.getAttribute('data-original') || 'reservado';
        }
    }

    // LÓGICA USUARIO
    function openUserReturnModal(id) {
        document.getElementById('user_modal_res_id').value = id;
        userReturnModal.show();
    }

    // ===== Modal Imagen (abrir/cerrar) =====
    (function() {
        const imageModalEl = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const titleEl = document.getElementById('imageModalTitle');

        document.addEventListener('click', function(e) {
            const el = e.target.closest('.js-open-img');
            if (!el) return;

            const src = el.getAttribute('data-fullsrc') || el.getAttribute('src');
            if (!src) return;

            modalImage.src = src;
            titleEl.textContent = el.getAttribute('data-title') || 'Vista previa';

            bootstrap.Modal.getOrCreateInstance(imageModalEl).show();
        });

        // Enter cuando tiene focus en miniatura
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            const el = document.activeElement;
            if (el && el.classList && el.classList.contains('js-open-img')) {
                const src = el.getAttribute('data-fullsrc') || el.getAttribute('src');
                if (!src) return;

                modalImage.src = src;
                titleEl.textContent = el.getAttribute('data-title') || 'Vista previa';
                bootstrap.Modal.getOrCreateInstance(imageModalEl).show();
            }
        });

        imageModalEl?.addEventListener('hidden.bs.modal', () => {
            modalImage.src = '';
        });
    })();

    // ===== AJAX ADMIN (statusForm) =====
    document.getElementById('statusForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const btn = document.getElementById('btnConfirmStatus');
        const old = btn ? btn.innerHTML : '';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Procesando...';
        }

        try {
            const fd = new FormData(form);

            const resp = await fetch('ajax/process_return_ajax.php', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let json;
            try {
                json = await resp.json();
            } catch {
                json = null;
            }

            if (!resp.ok || !json || !json.ok) {
                const msg = (json && json.message) ? json.message : 'No se pudo actualizar.';
                alert(msg);
                cancelStatusChange();
                return;
            }

            // ÉXITO: Recargamos para que se vea la nota nueva en la tabla
            location.reload();

        } catch (err) {
            console.error(err);
            alert('Error de red o del servidor.');
            cancelStatusChange();
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = old;
            }
        }
    });

    // ===== AJAX USUARIO (userReturnForm) =====
    document.getElementById('userReturnForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const old = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Enviando...';
        }

        try {
            const fd = new FormData(form);

            const resp = await fetch('ajax/process_return_ajax.php', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let json;
            try {
                json = await resp.json();
            } catch {
                json = null;
            }

            if (!resp.ok || !json || !json.ok) {
                const msg = (json && json.message) ? json.message : 'No se pudo solicitar devolución.';
                alert(msg);
                return;
            }

            const rid = json.data?.reservation_id;
            const status = (json.data?.status || '').toLowerCase();

            userReturnModal.hide();

            if (rid && status) {
                applyReservationUI(rid, status);
            }

        } catch (err) {
            console.error(err);
            alert('Error de red o del servidor.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = old;
            }
        }
    });
    </script>

    <?php include("footer.php"); ?>
</body>

</html>