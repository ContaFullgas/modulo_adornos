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
$user_dept_id = (int)current_user()['department_id'];

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
$total_items = $count_res ? $count_res->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_items / $items_per_page);

// CONSULTA PRINCIPAL
$sql = "
    SELECT r.*, d.name AS dept_name,
           i.code AS item_code, i.description AS item_description, i.image AS item_image,
           u.username as user_name
    FROM reservations r
    JOIN departments d ON d.id = r.dept_id
    JOIN items i ON i.id = r.item_id
    LEFT JOIN users u ON u.id = r.user_id
    $whereClause
    ORDER BY r.reserved_at DESC
    LIMIT $offset, $items_per_page
";
$result = $conn->query($sql);
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

    /* Modales */
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
                        
                        $status_raw = strtolower(trim($row['status'] ?? ''));
                        $isAdmin = (current_user()['role'] === 'admin');
                        $isMyDept = ($user_dept_id > 0 && (int)$row['dept_id'] === $user_dept_id);
                        
                        $selectClass = 'text-dark bg-light';
                        if ($status_raw === 'reservado') $selectClass = 'sel-red';
                        elseif ($status_raw === 'en proceso' || $status_raw === 'en_proceso') $selectClass = 'sel-yellow';
                        elseif ($status_raw === 'finalizado' || $status_raw === 'devuelto') $selectClass = 'sel-green';

                        $dateStr = (new DateTime($row['reserved_at']))->format('d M, Y');
                        $timeStr = (new DateTime($row['reserved_at']))->format('h:i A');
                ?>
                        <tr>
                            <td class="fw-medium"><?= $dept_name ?></td>
                            <td>
                                <?php if(!empty($item_image)): ?>
                                <img src="uploads/<?= htmlspecialchars($item_image) ?>" class="img-thumb" alt="img">
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
                            <td>
                                <?php if($status_raw === 'reservado'): ?>
                                <span class="status-pill st-apartado">Apartado</span>
                                <?php elseif($status_raw === 'en proceso' || $status_raw === 'en_proceso'): ?>
                                <span class="status-pill st-proceso">En Proceso</span>
                                <?php else: ?>
                                <span class="status-pill st-gris">Devuelto</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium small text-dark"><?= $dateStr ?></span>
                                    <span class="text-muted small" style="font-size: 0.75rem;"><?= $timeStr ?></span>
                                </div>
                            </td>
                            <td class="text-end">

                                <?php if ($isAdmin): ?>
                                <select class="form-select form-select-sm admin-select <?= $selectClass ?>"
                                    data-id="<?= $rid ?>" data-original="<?= $status_raw ?>"
                                    onchange="openStatusModal(this)">
                                    <option value="reservado" <?= $status_raw === 'reservado' ? 'selected' : '' ?>>🔴 No
                                        Recibido</option>
                                    <option value="en_proceso"
                                        <?= ($status_raw === 'en proceso' || $status_raw === 'en_proceso') ? 'selected' : '' ?>>
                                        🟡 Pendiente</option>
                                    <option value="finalizado"
                                        <?= ($status_raw === 'finalizado' || $status_raw === 'devuelto') ? 'selected' : '' ?>>
                                        🟢 Recibido</option>
                                </select>

                                <?php else: ?>
                                <?php if ($status_raw === 'reservado' && $isMyDept): ?>
                                <button class="btn-outline-custom" onclick="openUserReturnModal(<?= $rid ?>)">
                                    <i class="fas fa-undo-alt"></i> Devolver
                                </button>
                                <?php elseif ($status_raw === 'en proceso' || $status_raw === 'en_proceso'): ?>
                                <span class="text-warning small fst-italic"><i class="fas fa-clock me-1"></i> Esperando
                                    Admin</span>
                                <?php else: ?>
                                <span class="text-muted small"><i class="fas fa-check-double me-1"></i>
                                    Completado</span>
                                <?php endif; ?>
                                <?php endif; ?>

                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No se encontraron registros.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-4 d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link"
                        href="?page=<?= $page - 1 ?>&status=<?= $statusFilter ?>&q=<?= urlencode($searchQuery) ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>"><a class="page-link"
                        href="?page=<?= $i ?>&status=<?= $statusFilter ?>&q=<?= urlencode($searchQuery) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link"
                        href="?page=<?= $page + 1 ?>&status=<?= $statusFilter ?>&q=<?= urlencode($searchQuery) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
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

                    <button type="submit" id="btnConfirmStatus" class="btn-confirm-modal">Confirmar Cambio</button>
                    <button type="button" class="btn btn-link text-muted mt-2 text-decoration-none"
                        data-bs-dismiss="modal" onclick="cancelStatusChange()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="userReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <form method="POST" action="process_return.php" class="modal-content">
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
                            Proceso"</strong> hasta que sea recibido.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let currentSelectElement = null;
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    const userReturnModal = new bootstrap.Modal(document.getElementById('userReturnModal'));

    // LÓGICA ADMIN
    function openStatusModal(selectElement) {
        currentSelectElement = selectElement;
        const newStatus = selectElement.value;
        const resId = selectElement.getAttribute('data-id');

        const iconContainer = document.getElementById('statusIconContainer');
        const textDisplay = document.getElementById('statusTextDisplay');
        const btnConfirm = document.getElementById('btnConfirmStatus');

        document.getElementById('modal_res_id').value = resId;
        document.getElementById('modal_new_status').value = newStatus;

        if (newStatus === 'reservado') {
            iconContainer.innerHTML = '<i class="fas fa-times-circle status-icon-large text-danger"></i>';
            textDisplay.textContent = 'No Recibido';
            btnConfirm.style.backgroundColor = '#dc2626';
            btnConfirm.textContent = 'Marcar como No Recibido';
        } else if (newStatus === 'en_proceso') {
            iconContainer.innerHTML = '<i class="fas fa-clock status-icon-large text-warning"></i>';
            textDisplay.textContent = 'Pendiente';
            btnConfirm.style.backgroundColor = '#d97706';
            btnConfirm.textContent = 'Marcar como Pendiente';
        } else if (newStatus === 'finalizado') {
            iconContainer.innerHTML = '<i class="fas fa-check-circle status-icon-large text-success"></i>';
            textDisplay.textContent = 'Recibido (Finalizado)';
            btnConfirm.style.backgroundColor = '#059669';
            btnConfirm.textContent = 'Confirmar Recepción';
        }
        statusModal.show();
    }

    function cancelStatusChange() {
        if (currentSelectElement) {
            currentSelectElement.value = currentSelectElement.getAttribute('data-original');
        }
    }

    // LÓGICA USUARIO
    function openUserReturnModal(id) {
        document.getElementById('user_modal_res_id').value = id;
        userReturnModal.show();
    }
    </script>
    <?php include("footer.php"); ?>
</body>

</html>