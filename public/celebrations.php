<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

function getCelebrationStyle($name) {
    $n = mb_strtolower($name, 'UTF-8');
    if (strpos($n, 'navidad') !== false || strpos($n, 'nochebuena') !== false) return ['icon' => 'fa-snowflake', 'color' => 'bg-christmas-red'];
    if (strpos($n, 'año nuevo') !== false || strpos($n, 'new year') !== false) return ['icon' => 'fa-champagne-glasses', 'color' => 'bg-gold'];
    if (strpos($n, 'halloween') !== false || strpos($n, 'muertos') !== false) return ['icon' => 'fa-ghost', 'color' => 'bg-halloween-orange'];
    if (strpos($n, 'independencia') !== false || strpos($n, 'patria') !== false || strpos($n, 'septiembre') !== false) return ['icon' => 'fa-flag', 'color' => 'bg-patriotic-green'];
    if (strpos($n, 'amor') !== false || strpos($n, 'valentin') !== false || strpos($n, 'amistad') !== false) return ['icon' => 'fa-heart', 'color' => 'bg-love-pink'];
    if (strpos($n, 'madre') !== false || strpos($n, 'padre') !== false) return ['icon' => 'fa-gift', 'color' => 'bg-purple'];
    if (strpos($n, 'primavera') !== false || strpos($n, 'pascua') !== false) return ['icon' => 'fa-sun', 'color' => 'bg-spring-yellow'];
    return ['icon' => 'fa-calendar-star', 'color' => 'bg-primary-soft'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Celebraciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --body-bg: #f8fafc;
        --text-dark: #0f172a;
        --text-gray: #64748b;
    }

    body {
        background-color: var(--body-bg);
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding-top: 110px;
        color: var(--text-dark);
    }

    .card-modern {
        background: #ffffff;
        border: none;
        border-radius: 24px;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .card-header-modern {
        padding: 2rem 2.5rem;
        background: #ffffff;
        border-bottom: 1px dashed #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .icon-box {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        transition: transform 0.3s ease;
    }

    .dept-row:hover .icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    /* Toggle Switch Mejorado */
    .toggle-switch {
        position: relative;
        width: 56px;
        height: 28px;
        margin-right: 12px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: all 0.3s;
        border-radius: 50px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: all 0.3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    input:checked+.toggle-slider {
        background-color: #10b981;
    }

    input:checked+.toggle-slider:before {
        transform: translateX(28px);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.4rem 0.9rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge.active {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-badge.inactive {
        background-color: #f1f5f9;
        color: #64748b;
    }

    .status-badge i {
        font-size: 0.7rem;
    }

    /* Colores de celebraciones */
    .bg-christmas-red {
        background: #ffe4e6;
        color: #e11d48;
    }

    .bg-gold {
        background: #fef9c3;
        color: #ca8a04;
    }

    .bg-halloween-orange {
        background: #ffedd5;
        color: #ea580c;
    }

    .bg-patriotic-green {
        background: #dcfce7;
        color: #15803d;
    }

    .bg-love-pink {
        background: #fce7f3;
        color: #db2777;
    }

    .bg-purple {
        background: #f3e8ff;
        color: #9333ea;
    }

    .bg-spring-yellow {
        background: #fefce8;
        color: #eab308;
    }

    .bg-primary-soft {
        background: #e0f2fe;
        color: #0284c7;
    }

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

    .dept-row {
        transition: background-color 0.2s ease;
    }

    .dept-row:hover {
        background-color: #f8fafc;
    }

    .dept-name-text {
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--text-dark);
        margin-bottom: 0;
    }

    .btn-action-group {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        align-items: center;
    }

    .btn-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
    }

    .btn-edit-modern {
        color: #f59e0b;
        border-color: #fef3c7;
    }

    .btn-edit-modern:hover {
        background: #fef3c7;
        color: #d97706;
        transform: translateY(-2px);
    }

    .btn-delete-modern {
        color: #ef4444;
        border-color: #fee2e2;
    }

    .btn-delete-modern:hover {
        background: #fee2e2;
        color: #b91c1c;
        transform: translateY(-2px);
    }

    .btn-add-custom {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
    }

    .btn-add-custom:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* Toast de notificación */
    .toast-custom {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    </style>
</head>

<body>
    <?php include("navbar.php"); ?>

    <!-- Toast de notificación -->
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <div class="toast-custom">
        <div class="alert alert-<?= isset($_SESSION['success']) ? 'success' : 'danger' ?> alert-dismissible fade show shadow-lg"
            role="alert">
            <i class="fas fa-<?= isset($_SESSION['success']) ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <strong><?= $_SESSION['success'] ?? $_SESSION['error'] ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php 
        unset($_SESSION['success'], $_SESSION['error']); 
    endif; 
    ?>

    <div class="container py-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fa-solid fa-gift me-2 text-primary"></i>Celebraciones</h2>
                    <p class="text-gray mb-0 small">Administra los eventos y festividades. Solo una puede estar activa.
                    </p>
                </div>

                <?php if(current_user()['role'] === 'admin'): ?>
                <button type="button" class="btn-add-custom" data-bs-toggle="modal"
                    data-bs-target="#addCelebrationModal">
                    <i class="fa-solid fa-plus me-2"></i>Nueva Celebración
                </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Festividad</th>
                            <th>Estado</th>
                            <?php if(current_user()['role'] === 'admin'): ?>
                            <th class="text-end">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                $res = $conn->query("SELECT * FROM celebrations ORDER BY is_active DESC, name");
                if ($res && $res->num_rows > 0):
                    while ($row = $res->fetch_assoc()):
                        $id = (int)$row['id'];
                        $name = htmlspecialchars($row['name']);
                        $is_active = (int)$row['is_active'];
                        $style = getCelebrationStyle($name);
                ?>
                        <tr class="dept-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3 <?= $style['color'] ?>">
                                        <i class="fa-solid <?= $style['icon'] ?>"></i>
                                    </div>
                                    <div class="dept-name-text"><?= $name ?></div>
                                </div>
                            </td>

                            <td>
                                <?php if ($is_active): ?>
                                <span class="status-badge active">
                                    <i class="fas fa-circle"></i> ACTIVA
                                </span>
                                <?php else: ?>
                                <span class="status-badge inactive">
                                    <i class="far fa-circle"></i> Inactiva
                                </span>
                                <?php endif; ?>
                            </td>

                            <?php if(current_user()['role'] === 'admin'): ?>
                            <td class="text-end">
                                <div class="btn-action-group">
                                    <!-- Toggle Switch -->
                                    <label class="toggle-switch" title="<?= $is_active ? 'Desactivar' : 'Activar' ?>">
                                        <input type="checkbox" class="toggle-celebration" data-id="<?= $id ?>"
                                            <?= $is_active ? 'checked' : '' ?>>
                                        <span class="toggle-slider"></span>
                                    </label>

                                    <button class="btn-circle btn-edit-modern" type="button" data-bs-toggle="modal"
                                        data-bs-target="#editCelebrationModal" data-id="<?= $id ?>"
                                        data-name="<?= $name ?>" title="Editar">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>

                                    <button class="btn-circle btn-delete-modern" type="button" data-bs-toggle="modal"
                                        data-bs-target="#deleteConfirmModal" data-id="<?= $id ?>"
                                        data-name="<?= $name ?>" title="Eliminar">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; 
                else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="fa-solid fa-calendar-xmark fa-3x mb-3 text-secondary"></i>
                                    <p class="h6 text-muted">No hay celebraciones registradas.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar -->
    <div class="modal fade" id="addCelebrationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="celebration_action.php?action=create" class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="mb-3 text-success opacity-75">
                        <i class="fa-solid fa-calendar-plus fa-4x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Nueva Celebración</h4>
                    <p class="text-muted mb-4">Registra un nuevo evento festivo.</p>

                    <input type="text" name="name" class="form-control mb-3" placeholder="Ej: San Valentín 2025"
                        required>
                    <button type="submit" class="btn btn-success w-100 fw-bold">Crear Celebración</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editCelebrationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="celebration_action.php?action=edit" class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="mb-3 text-warning opacity-75">
                        <i class="fa-solid fa-pen-to-square fa-4x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Editar Celebración</h4>
                    <p class="text-muted mb-4">Modifica el nombre del evento.</p>

                    <input type="hidden" name="id" id="edit_celebration_id">
                    <input type="text" name="name" id="edit_celebration_name" class="form-control mb-3" required>
                    <button type="submit" class="btn btn-warning w-100 fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="celebration_action.php?action=delete" class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="fa-solid fa-circle-exclamation fa-4x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">¿Estás seguro?</h4>
                    <p class="text-muted mb-4">
                        Vas a eliminar <strong id="delete_celeb_name" class="text-dark"></strong>.<br>
                        Esta acción no se puede deshacer.
                    </p>
                    <input type="hidden" name="id" id="delete_celeb_id">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">Sí, eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toggle activar/desactivar celebración
    document.querySelectorAll('.toggle-celebration').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const isActive = this.checked ? 1 : 0;

            fetch('celebration_action.php?action=toggle_active', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&is_active=${isActive}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Recargar para actualizar el tema
                    } else {
                        alert('Error: ' + data.message);
                        this.checked = !this.checked;
                    }
                })
                .catch(() => {
                    alert('Error de conexión');
                    this.checked = !this.checked;
                });
        });
    });

    // Modal editar
    document.getElementById('editCelebrationModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        document.getElementById('edit_celebration_id').value = btn.dataset.id;
        document.getElementById('edit_celebration_name').value = btn.dataset.name;
    });

    // Modal eliminar
    document.getElementById('deleteConfirmModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        document.getElementById('delete_celeb_id').value = btn.dataset.id;
        document.getElementById('delete_celeb_name').textContent = '«' + btn.dataset.name + '»';
    });

    // Auto-ocultar toast después de 5 segundos
    setTimeout(() => {
        document.querySelectorAll('.toast-custom .alert').forEach(alert => {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        });
    }, 5000);
    </script>
    <?php include("footer.php"); ?>
</body>

</html>