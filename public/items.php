<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
require_once __DIR__ . '/temas.php';

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

    /* Estilos Card Originales */
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

    .item-card.out-of-stock .card-img-wrapper img,
    .item-card.season-closed .card-img-wrapper img {
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
        display: flex;
        flex-direction: column;
        gap: 5px;
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

    /* === DISEÑO DE PAGINACIÓN ORIGINAL === */
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

    /* === DISEÑO MODALES ORIGINAL === */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header-custom {
        padding: 1.5rem 1.5rem 0 1.5rem;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
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
    }

    .modal-icon-circle.warning {
        background-color: #fffbeb;
        color: #f59e0b;
    }

    .modal-title-custom {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.2rem;
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

    .btn-save-modal.warning {
        background-color: #f59e0b;
    }

    .filter-bar {
        background: white;
        padding: 0.6rem 1rem;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        flex-grow: 1;
    }

    .btn-add-custom {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 100%;
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
                            while($c = $celebs->fetch_assoc()){
                                $selected = ($sel == $c['id']) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
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
            $where = $sel ? "WHERE i.celebration_id = $sel" : "";
            
            // Total para paginación
            $count_res = $conn->query("SELECT COUNT(*) as total FROM items i $where");
            $total_items = $count_res->fetch_assoc()['total'];
            $total_pages = ceil($total_items / $items_per_page);

            // Reservas
            $reserved_map = [];
            $res_group = $conn->query("SELECT r.item_id, d.name as dept_name, SUM(r.quantity) as qty FROM reservations r JOIN departments d ON d.id = r.dept_id WHERE LOWER(r.status) = 'reservado' GROUP BY r.item_id, d.id, d.name");
            while($rg = $res_group->fetch_assoc()){
                $reserved_map[$rg['item_id']][] = htmlspecialchars($rg['dept_name']) . " (" . $rg['qty'] . ")";
            }

            // Consulta principal (Season Logic)
            $query = "SELECT i.*, c.is_active as season_active, c.name as celebration_name 
                      FROM items i 
                      LEFT JOIN celebrations c ON i.celebration_id = c.id 
                      $where ORDER BY i.code LIMIT $offset, $items_per_page";
            $items_res = $conn->query($query);

            while ($row = $items_res->fetch_assoc()):
                $item_id = $row['id'];
                $avail = (int)$row['available_quantity'];
                $season_active = isset($row['season_active']) ? (int)$row['season_active'] : 1;
                $is_closed = ($season_active === 0);
                $is_out = ($avail <= 0);
                $cardClass = $is_out ? 'out-of-stock' : ($is_closed ? 'season-closed' : '');
            ?>
            <div class="col-sm-6 col-lg-4 col-xl-3" id="item-col-<?= $item_id ?>">
                <div class="card item-card <?= $cardClass ?>" id="item-card-<?= $item_id ?>">
                    <div class="card-img-wrapper">
                        <?php if($row['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="item-img"
                            data-fullsrc="uploads/<?= htmlspecialchars($row['image']) ?>" style="cursor: zoom-in;">
                        <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light text-secondary"><i
                                class="fas fa-image fa-3x opacity-25"></i></div>
                        <?php endif; ?>

                        <div class="status-overlay">
                            <?php if($is_out): ?> <span class="badge bg-danger shadow-sm"><i
                                    class="fas fa-ban me-1"></i>Agotado</span> <?php endif; ?>
                            <?php if($is_closed): ?> <span class="badge bg-warning text-dark shadow-sm"><i
                                    class="fas fa-calendar-times me-1"></i>Temporada Cerrada</span> <?php endif; ?>
                        </div>

                        <?php if($row['celebration_name']): ?>
                        <div class="badge-overlay"><?= htmlspecialchars($row['celebration_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <h5 class="item-code">Código: <?= htmlspecialchars($row['code']) ?></h5>
                        <p class="item-desc text-truncate-2"><?= nl2br(htmlspecialchars($row['description'])) ?></p>

                        <div class="stats-container">
                            <div class="stat-item border-end w-50"><span class="text-muted small">Total</span><span
                                    class="stat-value"><?= $row['total_quantity'] ?></span></div>
                            <div class="stat-item w-50"><span class="text-muted small">Disponibles</span><span
                                    class="stat-value <?= $avail > 0 ? 'text-success' : 'text-danger' ?>"><?= $avail ?></span>
                            </div>
                        </div>

                        <?php if(isset($reserved_map[$item_id])): ?>
                        <div class="reserved-info"><i class="fas fa-lock me-1"></i> <strong>Apartado
                                por:</strong><br><?= implode(', ', $reserved_map[$item_id]) ?></div>
                        <?php endif; ?>

                        <div class="action-buttons">
                            <?php 
                            $can_res = ($avail > 0 && !$is_closed);
                            $btn_txt = $is_closed ? 'Temporada Cerrada' : ($is_out ? 'No disponible' : 'Reservar');
                            $btn_icon = $is_closed ? 'fa-calendar-times' : ($is_out ? 'fa-ban' : 'fa-cart-plus');
                            ?>
                            <button class="btn <?= $can_res ? 'btn-primary' : 'btn-secondary' ?> flex-grow-1 rounded-3"
                                <?= $can_res ? 'data-bs-toggle="modal" data-bs-target="#reserveModal"' : 'disabled' ?>
                                data-itemid="<?= $item_id ?>" data-code="<?= $row['code'] ?>"
                                data-available="<?= $avail ?>">
                                <i class="fas <?= $btn_icon ?> me-1"></i> <?= $btn_txt ?>
                            </button>

                            <?php if(current_user()['role'] === 'admin'): ?>
                            <div class="dropdown">
                                <button class="btn btn-light border" type="button" data-bs-toggle="dropdown"><i
                                        class="fas fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu">
                                    <li><button class="dropdown-item js-open-edit" data-bs-toggle="modal"
                                            data-bs-target="#editItemModal" data-id="<?= $item_id ?>"
                                            data-code="<?= $row['code'] ?>"
                                            data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                                            data-total="<?= $row['total_quantity'] ?>" data-available="<?= $avail ?>"
                                            data-image="<?= htmlspecialchars($row['image'], ENT_QUOTES) ?>"
                                            data-celebration="<?= $row['celebration_id'] ?>"><i
                                                class="fas fa-pen me-2 text-warning"></i> Editar</button></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><button class="dropdown-item text-danger js-open-delete" data-bs-toggle="modal"
                                            data-bs-target="#deleteConfirmModal" data-id="<?= $item_id ?>"
                                            data-code="<?= $row['code'] ?>"><i class="fas fa-trash me-2"></i>
                                            Eliminar</button></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $page - 1 ?><?= $sel ? '&celebration='.$sel : '' ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $sel ? '&celebration='.$sel : '' ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $page + 1 ?><?= $sel ? '&celebration='.$sel : '' ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form method="POST" action="add_item.php" enctype="multipart/form-data" class="modal-content"
                id="addItemForm">
                <div class="modal-header-custom">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
                    <div class="modal-icon-circle"><i class="fas fa-box-open"></i></div>
                    <h3 class="modal-title-custom">Nuevo Adorno</h3>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="form-floating mb-2"><input name="code" class="form-control" placeholder="C"
                            required><label>Código</label></div>
                    <div class="form-floating mb-2"><input type="number" name="total_quantity" class="form-control"
                            min="1" value="1" required><label>Total</label></div>
                    <div class="form-floating mb-2">
                        <select name="celebration_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php 
                            $cs = $conn->query("SELECT id, name FROM celebrations ORDER BY name");
                            while($c = $cs->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['name']}</option>";
                            ?>
                        </select>
                        <label>Celebración</label>
                    </div>
                    <div class="mb-2"><label class="form-label small text-muted">Imagen</label><input type="file"
                            name="image" class="form-control form-control-sm" accept="image/*"></div>
                    <div class="form-floating mb-3"><textarea name="description" class="form-control"
                            style="height: 80px"></textarea><label>Descripción</label></div>
                    <button type="submit" class="btn-save-modal">Guardar Adorno <i
                            class="fas fa-arrow-right ms-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form id="editItemForm" class="modal-content">
                <div class="modal-header-custom">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
                    <div class="modal-icon-circle warning"><i class="fas fa-pen-nib"></i></div>
                    <h3 class="modal-title-custom">Editar Adorno</h3>
                </div>
                <div class="modal-body p-4 pt-0">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-floating mb-2"><input name="code" id="edit_code" class="form-control"
                            required><label>Código</label></div>
                    <div class="form-floating mb-2"><input type="number" name="total_quantity" id="edit_total"
                            class="form-control" required><label>Total</label></div>
                    <div class="form-floating mb-2">
                        <select name="celebration_id" id="edit_celebration" class="form-select" required>
                            <?php 
                            $cs2 = $conn->query("SELECT id, name FROM celebrations ORDER BY name");
                            while($c2 = $cs2->fetch_assoc()) echo "<option value='{$c2['id']}'>{$c2['name']}</option>";
                            ?>
                        </select>
                        <label>Celebración</label>
                    </div>
                    <input type="file" name="image" class="form-control form-control-sm mb-2">
                    <div class="form-floating mb-3"><textarea name="description" id="edit_description"
                            class="form-control" style="height: 80px"></textarea><label>Descripción</label></div>
                    <button type="submit" class="btn-save-modal warning">Guardar Cambios <i
                            class="fas fa-check ms-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form id="deleteItemForm" class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                    </div>
                    <h4 class="fw-bold">¿Eliminar Adorno?</h4>
                    <p class="text-muted small">Vas a eliminar este artículo código <strong id="delete_code"></strong>.
                        Esta acción no se puede deshacer.</p>
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger flex-grow-1 fw-bold">Sí, eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="reserveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form id="reserveForm" class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-shopping-bag me-2"></i>Reservar Adorno</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="item_id" id="res_item_id">
                    <h3 id="res_code_display" class="text-center fw-bold text-primary mb-1"></h3>
                    <p id="res_avail_display" class="text-center text-muted small mb-3"></p>
                    <div class="form-floating mb-3">
                        <select name="dept_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php 
                            $ds = $conn->query("SELECT id, name FROM departments ORDER BY name");
                            while($d = $ds->fetch_assoc()) echo "<option value='{$d['id']}'>{$d['name']}</option>";
                            ?>
                        </select><label>Departamento</label>
                    </div>
                    <div class="form-floating mb-3"><input type="number" name="quantity" id="res_qty"
                            class="form-control" value="1" min="1" required><label>Cantidad</label></div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold p-2">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="background: #e6ffe6;">
                <div class="modal-header border-0 px-4 py-3" style="background: #2d5a3d;">
                    <h5 class="modal-title text-white"><i class="fas fa-image me-2"></i>Vista previa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <img id="modalImage" src="" class="img-fluid rounded-4 shadow" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Scripts de Carga de Modales
    document.getElementById('editItemModal').addEventListener('show.bs.modal', function(e) {
        const b = e.relatedTarget;
        document.getElementById('edit_id').value = b.dataset.id;
        document.getElementById('edit_code').value = b.dataset.code;
        document.getElementById('edit_total').value = b.dataset.total;
        document.getElementById('edit_description').value = b.dataset.description;
        document.getElementById('edit_celebration').value = b.dataset.celebration;
    });

    document.getElementById('reserveModal').addEventListener('show.bs.modal', function(e) {
        const b = e.relatedTarget;
        document.getElementById('res_item_id').value = b.dataset.itemid;
        document.getElementById('res_code_display').innerText = 'Código: ' + b.dataset.code;
        document.getElementById('res_avail_display').innerText = 'Stock disponible: ' + b.dataset.available;
        document.getElementById('res_qty').max = b.dataset.available;
    });

    document.getElementById('deleteConfirmModal').addEventListener('show.bs.modal', function(e) {
        document.getElementById('delete_id').value = e.relatedTarget.dataset.id;
        document.getElementById('delete_code').innerText = e.relatedTarget.dataset.code;
    });

    // AJAX Formularios
    const handleAjax = async (id, url) => {
        document.getElementById(id).addEventListener('submit', async function(e) {
            e.preventDefault();
            const resp = await fetch(url, {
                method: 'POST',
                body: new FormData(this)
            });
            const res = await resp.json();
            if (res.ok) location.reload();
            else alert(res.message);
        });
    };

    handleAjax('reserveForm', 'ajax/reserve_ajax.php');
    handleAjax('editItemForm', 'ajax/item_action_ajax.php?action=edit');
    handleAjax('deleteItemForm', 'ajax/item_action_ajax.php?action=delete');

    // Zoom Imagen
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('item-img')) {
            document.getElementById('modalImage').src = e.target.dataset.fullsrc;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('imageModal')).show();
        }
    });
    </script>

    <?php include("footer.php"); ?>
</body>

</html>