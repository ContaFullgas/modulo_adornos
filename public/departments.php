<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

// Función auxiliar para obtener icono y color según el nombre del departamento
function getDeptStyle($name) {
    $n = mb_strtolower($name, 'UTF-8');
    
    // Diccionario de estilos
    if (strpos($n, 'sist') !== false || strpos($n, 'tec') !== false || strpos($n, 'it') !== false || strpos($n, 'dev') !== false) {
        return ['icon' => 'fa-laptop-code', 'color' => 'bg-indigo'];
    }
    if (strpos($n, 'ven') !== false || strpos($n, 'comer') !== false || strpos($n, 'mark') !== false) {
        return ['icon' => 'fa-chart-line', 'color' => 'bg-emerald'];
    }
    if (strpos($n, 'rec') !== false || strpos($n, 'hum') !== false || strpos($n, 'rh') !== false || strpos($n, 'pers') !== false) {
        return ['icon' => 'fa-user-group', 'color' => 'bg-rose'];
    }
    if (strpos($n, 'fin') !== false || strpos($n, 'con') !== false || strpos($n, 'adm') !== false || strpos($n, 'pag') !== false) {
        return ['icon' => 'fa-file-invoice-dollar', 'color' => 'bg-amber'];
    }
    if (strpos($n, 'alm') !== false || strpos($n, 'inv') !== false || strpos($n, 'log') !== false || strpos($n, 'bod') !== false) {
        return ['icon' => 'fa-boxes-stacked', 'color' => 'bg-orange'];
    }
    if (strpos($n, 'dir') !== false || strpos($n, 'ger') !== false || strpos($n, 'jef') !== false) {
        return ['icon' => 'fa-briefcase', 'color' => 'bg-slate'];
    }
    if (strpos($n, 'seg') !== false || strpos($n, 'vig') !== false) {
        return ['icon' => 'fa-shield-halved', 'color' => 'bg-blue'];
    }
    
    // Default
    return ['icon' => 'fa-building', 'color' => 'bg-primary-soft'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Departamentos</title>
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

    /* Contenedor Principal */
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

    /* Estilos de Iconos Dinámicos */
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
        transform: scale(1.1) rotate(-5deg);
    }

    /* Variantes de Color */
    .bg-indigo {
        background: #e0e7ff;
        color: #4338ca;
    }

    .bg-emerald {
        background: #d1fae5;
        color: #059669;
    }

    .bg-rose {
        background: #ffe4e6;
        color: #e11d48;
    }

    .bg-amber {
        background: #fef3c7;
        color: #d97706;
    }

    .bg-orange {
        background: #ffedd5;
        color: #ea580c;
    }

    .bg-slate {
        background: #f1f5f9;
        color: #475569;
    }

    .bg-blue {
        background: #dbeafe;
        color: #2563eb;
    }

    .bg-primary-soft {
        background: #e0f2fe;
        color: #0284c7;
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

    /* Botones de Acción (Tabla) */
    .btn-action-group {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
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

    /* === BOTÓN AGREGAR (VERDE) === */
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

    /* === MODAL ESTILIZADO === */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    .modal-header-custom {
        padding: 2rem 2rem 0 2rem;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .btn-close-custom {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 10;
    }

    /* Icono círculo en el modal (Estilo AGREGAR - Verde) */
    .modal-icon-circle {
        width: 80px;
        height: 80px;
        background-color: #ecfdf5;
        color: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 1rem;
        animation: pulse-soft 2s infinite;
    }

    /* Icono círculo en el modal (Estilo EDITAR - Ámbar) */
    .modal-icon-circle.warning {
        background-color: #fffbeb;
        color: #f59e0b;
    }

    @keyframes pulse-soft {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.2);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .modal-title-custom {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .modal-subtitle {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .modal-body-custom {
        padding: 0 2.5rem 2.5rem 2.5rem;
    }

    /* Inputs Flotantes */
    .form-floating>.form-control {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
    }

    .form-floating>.form-control:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    /* Focus específico para editar */
    .form-control.edit-input:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    }

    .form-floating>label {
        color: #94a3b8;
    }

    /* Botón Guardar en Modal (Verde) */
    .btn-save-modal {
        width: 100%;
        background-color: #10b981;
        color: white;
        padding: 1rem;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        transition: all 0.3s;
    }

    .btn-save-modal:hover {
        background-color: #059669;
        transform: translateY(-2px);
    }

    /* Botón Editar en Modal (Ámbar) */
    .btn-save-modal.warning {
        background-color: #f59e0b;
    }

    .btn-save-modal.warning:hover {
        background-color: #d97706;
    }
    </style>
</head>

<body>
    <?php include("navbar.php"); ?>

    <div class="container py-4">

        <div class="card-modern">
            <div class="card-header-modern">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fa-duotone fa-buildings me-2 text-primary"></i>Organización</h2>
                    <p class="text-gray mb-0 small">Administra las áreas y departamentos activos.</p>
                </div>

                <?php if(current_user()['role'] === 'admin'): ?>
                <button type="button" class="btn-add-custom" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                    <i class="fa-solid fa-plus me-2"></i>Agregar Área
                </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <?php if(current_user()['role'] === 'admin'): ?>
                            <th class="text-end">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                $result = $conn->query("SELECT * FROM departments ORDER BY name");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $id = (int)$row['id'];
                        $name = htmlspecialchars($row["name"]);
                        $style = getDeptStyle($name);
                ?>
                        <tr class="dept-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3 <?= $style['color'] ?>">
                                        <i class="fa-solid <?= $style['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <div class="dept-name-text"><?= $name ?></div>
                                    </div>
                                </div>
                            </td>

                            <?php if(current_user()['role'] === 'admin'): ?>
                            <td class="text-end">
                                <div class="btn-action-group">
                                    <button class="btn-circle btn-edit-modern" type="button" data-bs-toggle="modal"
                                        data-bs-target="#editDeptModal" data-id="<?= $id ?>" data-name="<?= $name ?>"
                                        title="Editar">
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
                            <td colspan="2" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                                    <p class="h6 text-muted">No hay departamentos registrados aún.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="department_action.php?action=create" class="modal-content">
                <div class="modal-header-custom">
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                    <div class="modal-icon-circle">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h3 class="modal-title-custom">Nuevo Departamento</h3>
                    <p class="modal-subtitle">Ingresa los detalles para registrar un área.</p>
                </div>
                <div class="modal-body-custom">
                    <div class="form-floating mb-4">
                        <input type="text" name="name" class="form-control" id="addNameInput"
                            placeholder="Ej: Recursos Humanos" required>
                        <label for="addNameInput">Nombre del Departamento</label>
                    </div>
                    <button type="submit" class="btn-save-modal">
                        Guardar Departamento <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editDeptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="department_action.php?action=edit" class="modal-content" id="editDeptForm">

                <div class="modal-header-custom">
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                    <div class="modal-icon-circle warning">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                    <h3 class="modal-title-custom">Editar Departamento</h3>
                    <p class="modal-subtitle">Modifica el nombre del área seleccionada.</p>
                </div>

                <div class="modal-body-custom">
                    <input type="hidden" name="id" id="edit_dept_id">
                    <div class="form-floating mb-4">
                        <input type="text" name="name" id="edit_dept_name" class="form-control edit-input"
                            placeholder="Nombre" required>
                        <label for="edit_dept_name">Nombre del Área</label>
                    </div>
                    <button type="submit" class="btn-save-modal warning">
                        Guardar Cambios <i class="fas fa-check ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="department_action.php?action=delete" class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="fa-solid fa-circle-exclamation fa-4x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">¿Estás seguro?</h4>
                    <p class="text-muted mb-4">
                        Vas a eliminar el departamento <strong id="delete_dept_name" class="text-dark"></strong>.<br>
                        Esta acción no se puede deshacer.
                    </p>
                    <input type="hidden" name="id" id="delete_dept_id">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 fw-medium"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">Sí, eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Lógica Modal Editar
    var editModal = document.getElementById('editDeptModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');

        document.getElementById('edit_dept_id').value = id;
        document.getElementById('edit_dept_name').value = name;

        setTimeout(function() {
            document.getElementById('edit_dept_name').focus();
        }, 400);
    });

    // Lógica Modal Eliminar
    var deleteModal = document.getElementById('deleteConfirmModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');

        document.getElementById('delete_dept_id').value = id;
        document.getElementById('delete_dept_name').textContent = '«' + name + '»';
    });
    </script>
    <?php include("footer.php"); ?>
</body>

</html>