<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
require_admin(); // Solo admin ve el historial

/* --- LÓGICA DE PAGINACIÓN --- */
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

/* 1. CONTAR TOTAL DE REGISTROS */
$count_res = $conn->query("SELECT COUNT(*) as total FROM returns");
$total_items = $count_res ? $count_res->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_items / $items_per_page);

/* 2. OBTENER DEVOLUCIONES DE ESTA PÁGINA */
$list = [];
$res = $conn->query("
    SELECT r.*, 
    i.code AS item_code,
    i.description AS item_desc,
    d.name AS dept_name, 
    u.username AS handled_by_user
    FROM returns r
    LEFT JOIN items i ON r.item_id = i.id
    LEFT JOIN departments d ON r.dept_id = d.id
    LEFT JOIN users u ON r.handled_by = u.id
    ORDER BY r.returned_at DESC
    LIMIT $offset, $items_per_page
");

while($row = $res->fetch_assoc()) $list[] = $row;
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Historial de Devoluciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --body-bg: #f8fafc;
        --text-dark: #0f172a;
        --text-gray: #64748b;
        --primary-accent: #6366f1;
        /* Indigo */
    }

    body {
        background-color: var(--body-bg);
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding-top: 110px;
        color: var(--text-dark);
    }

    /* Card Principal */
    .card-modern {
        background: #ffffff;
        border: none;
        border-radius: 24px;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 600px;
    }

    .card-header-modern {
        padding: 2rem 2.5rem;
        background: #ffffff;
        border-bottom: 1px dashed #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Tabla */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern th {
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        color: var(--text-gray);
        font-weight: 700;
        padding: 1.5rem 2.5rem;
        background: #fcfcfc;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-modern td {
        padding: 1.25rem 2.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
    }

    .table-modern tr:last-child td {
        border-bottom: none;
    }

    .return-row {
        transition: background-color 0.2s ease;
    }

    .return-row:hover {
        background-color: #f8fafc;
    }

    /* Estilos Específicos */
    .date-badge {
        font-weight: 600;
        color: var(--text-dark);
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .date-badge span {
        font-size: 0.8rem;
        color: var(--text-gray);
        font-weight: 400;
    }

    .item-info {
        font-weight: 600;
        color: #334155;
    }

    .item-desc {
        font-size: 0.85rem;
        color: #94a3b8;
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .qty-badge {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .user-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Paginación */
    .pagination-container {
        padding: 1.5rem 2.5rem;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
    }

    .pagination .page-link {
        color: var(--text-dark);
        border: none;
        margin: 0 3px;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary-accent);
        color: white;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2);
    }

    .pagination .page-link:hover:not(.active) {
        background-color: #f1f5f9;
        color: var(--primary-accent);
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }
    </style>
</head>

<body>

    <?php include("navbar.php"); ?>

    <div class="container py-4">

        <div class="card-modern">
            <div class="card-header-modern">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Historial de Devoluciones
                    </h2>
                    <p class="text-gray mb-0 small">Registro de items devueltos al inventario.</p>
                </div>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Artículo</th>
                            <th>Departamento</th>
                            <th>Cantidad</th>
                            <th>Notas</th>
                            <th class="text-end">Registrado Por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($list) > 0): ?>
                        <?php foreach($list as $r): 
                            $dateObj = new DateTime($r['returned_at']);
                            $dateStr = $dateObj->format('d M, Y');
                            $timeStr = $dateObj->format('h:i A');
                        ?>
                        <tr class="return-row">
                            <td>
                                <div class="date-badge">
                                    <?= $dateStr ?>
                                    <span><?= $timeStr ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="item-info">
                                    <i class="fa-solid fa-barcode me-1 text-muted small"></i>
                                    <?= htmlspecialchars($r['item_code'] ?? 'N/A') ?>
                                </div>
                                <div class="item-desc" title="<?= htmlspecialchars($r['item_desc'] ?? '') ?>">
                                    <?= htmlspecialchars($r['item_desc'] ?? 'Sin descripción') ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center text-secondary">
                                    <i class="fa-regular fa-building me-2"></i>
                                    <?= htmlspecialchars($r['dept_name'] ?? 'General') ?>
                                </div>
                            </td>

                            <td>
                                <span class="qty-badge">+<?= $r['quantity'] ?></span>
                            </td>

                            <td class="text-muted small">
                                <?php if(!empty($r['notes'])): ?>
                                <i class="fa-regular fa-comment-dots me-1"></i> <?= htmlspecialchars($r['notes']) ?>
                                <?php else: ?>
                                <span class="opacity-50">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <span class="user-pill">
                                    <i class="fa-solid fa-user-shield"></i>
                                    <?= htmlspecialchars($r['handled_by_user'] ?? 'Sistema') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="fa-solid fa-box-open fa-3x mb-3 text-secondary"></i>
                                    <p class="h6 text-muted">No hay devoluciones registradas.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav class="d-flex justify-content-center">
                    <ul class="pagination mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("footer.php"); ?>
</body>

</html>