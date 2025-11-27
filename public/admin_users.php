<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();

/* --- LÓGICA PAGINACIÓN --- */
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

/* 1. CONTAR TOTAL DE USUARIOS */
$count_res = $conn->query("SELECT COUNT(*) as total FROM users");
$total_items = $count_res ? $count_res->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_items / $items_per_page);

/* 2. OBTENER USUARIOS DE ESTA PÁGINA */
$users = [];
$res = $conn->query("
    SELECT u.*, d.name AS department_name
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.id
    ORDER BY u.created_at DESC
    LIMIT $offset, $items_per_page
");
while($r = $res->fetch_assoc()) $users[] = $r;

/* GET DEPARTMENTS FOR SELECT */
$departments = [];
$dr = $conn->query("SELECT * FROM departments ORDER BY name");
while($d = $dr->fetch_assoc()) $departments[] = $d;
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --body-bg: #f8fafc;
        --text-dark: #0f172a;
        --text-gray: #64748b;
        --primary-accent: #10b981;
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

    /* Botón Agregar (Verde) */
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

    .user-row {
        transition: background-color 0.2s ease;
    }

    .user-row:hover {
        background-color: #f8fafc;
    }

    /* Avatares de Rol */
    .role-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 15px;
    }

    .avatar-admin {
        background: #fef3c7;
        color: #d97706;
    }

    .avatar-user {
        background: #e0e7ff;
        color: #4338ca;
    }

    /* Badges */
    .badge-role {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-admin-style {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fcd34d;
    }

    .badge-user-style {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    /* Botones Acción */
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
        transition: all 0.2s;
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
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }

    .pagination .page-link:hover:not(.active) {
        background-color: #f1f5f9;
        color: var(--primary-accent);
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Modales */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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

    .modal-icon-circle.edit {
        background-color: #e0e7ff;
        color: #4f46e5;
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

    /* Inputs */
    .form-floating>.form-control,
    .form-floating>.form-select {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
    }

    .form-floating>.form-control:focus,
    .form-floating>.form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    .form-floating>label {
        color: #94a3b8;
    }

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
    </style>
</head>

<body>

    <?php include("navbar.php"); ?>

    <div class="container py-4">

        <div class="card-modern">
            <div class="card-header-modern">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fa-solid fa-users-gear me-2 text-primary"></i>Gestión de Usuarios
                    </h2>
                    <p class="text-gray mb-0 small">Administra los accesos y roles del sistema.</p>
                </div>

                <button class="btn-add-custom" onclick="openAddModal()">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo Usuario
                </button>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Rol</th>
                            <th>Departamento</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($users) > 0): ?>
                        <?php foreach($users as $u): 
                            $isAdmin = ($u['role'] === 'admin');
                        ?>
                        <tr class="user-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="role-avatar <?= $isAdmin ? 'avatar-admin' : 'avatar-user' ?>">
                                        <i class="fa-solid <?= $isAdmin ? 'fa-user-shield' : 'fa-user' ?>"></i>
                                    </div>
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($u['username'] ?? '') ?>
                                        <div class="small text-muted fw-normal">ID: #<?= $u['id'] ?></div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-secondary fw-medium">
                                <?= htmlspecialchars($u['full_name'] ?? '---') ?>
                            </td>

                            <td>
                                <?php if($isAdmin): ?>
                                <span class="badge-role badge-admin-style"><i
                                        class="fa-solid fa-crown me-1"></i>Admin</span>
                                <?php else: ?>
                                <span class="badge-role badge-user-style">Usuario</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-muted">
                                <?= htmlspecialchars($u['department_name'] ?? 'Sin Depto.') ?>
                            </td>

                            <td class="text-end">
                                <div class="btn-action-group">
                                    <button class="btn-circle btn-edit-modern"
                                        onclick='openEditModal(<?= json_encode($u) ?>)' title="Editar Usuario">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>

                                    <button class="btn-circle btn-delete-modern"
                                        onclick="openDeleteModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')"
                                        title="Eliminar Usuario">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No hay usuarios registrados.
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


    <div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="admin_user_action.php" class="modal-content" id="userForm">

                <div class="modal-header-custom">
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                    <div class="modal-icon-circle edit" id="modalIcon">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="modal-title-custom" id="modalTitle">Nuevo Usuario</h3>
                    <p class="modal-subtitle">Configura los datos de acceso.</p>
                </div>

                <div class="modal-body-custom">
                    <input type="hidden" name="id" id="user_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="username" id="user_username" class="form-control"
                                    placeholder="Usuario" required>
                                <label>Usuario (Login)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="user_password" class="form-control"
                                    placeholder="Contraseña">
                                <label>Contraseña</label>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted mb-3 ms-1 small" id="passHelp" style="display:none;">
                        <i class="fa-solid fa-info-circle me-1"></i>Deja la contraseña vacía para no cambiarla.
                    </small>

                    <div class="form-floating mb-3">
                        <input type="text" name="full_name" id="user_fullname" class="form-control"
                            placeholder="Nombre completo">
                        <label>Nombre Completo</label>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="role" id="user_role" class="form-select">
                                    <option value="usuario">Usuario Estándar</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <label>Rol del Sistema</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="department_id" id="user_department" class="form-select">
                                    <option value="">-- Sin Departamento --</option>
                                    <?php foreach($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Departamento</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-modal">
                        Guardar Datos <i class="fas fa-check ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="admin_user_action.php?action=delete" class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="fa-solid fa-user-xmark fa-4x"></i>
                    </div>
                    <h4 class="fw-bold mb-2">¿Eliminar Usuario?</h4>
                    <p class="text-muted mb-4">
                        Vas a eliminar a <strong id="del_username_display" class="text-dark"></strong>.<br>
                        Esta acción revocará su acceso inmediatamente.
                    </p>
                    <input type="hidden" name="id" id="del_user_id">
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
    const userModal = new bootstrap.Modal(document.getElementById('modalUser'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

    // Lógica APERTURA MODAL AGREGAR
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
        document.getElementById('modalIcon').className = 'modal-icon-circle';
        document.getElementById('modalIcon').innerHTML = '<i class="fa-solid fa-user-plus"></i>';

        document.getElementById('user_id').value = '0'; // ID 0 = Nuevo
        document.getElementById('user_username').value = '';
        document.getElementById('user_fullname').value = '';
        document.getElementById('user_password').value = '';

        // OCULTAR TEXTO AYUDA (CRUCIAL)
        document.getElementById('passHelp').style.display = 'none';

        // OBLIGATORIO EN CREACIÓN
        document.getElementById('user_password').required = true;

        document.getElementById('user_role').value = 'usuario';
        document.getElementById('user_department').value = '';

        userModal.show();
    }

    // Lógica APERTURA MODAL EDITAR
    function openEditModal(user) {
        document.getElementById('modalTitle').textContent = 'Editar Usuario';
        document.getElementById('modalIcon').className = 'modal-icon-circle edit';
        document.getElementById('modalIcon').innerHTML = '<i class="fa-solid fa-user-pen"></i>';

        document.getElementById('user_id').value = user.id;
        document.getElementById('user_username').value = user.username;
        document.getElementById('user_fullname').value = user.full_name;

        // MOSTRAR TEXTO AYUDA
        document.getElementById('passHelp').style.display = 'block';

        // OPCIONAL EN EDICIÓN
        document.getElementById('user_password').value = '';
        document.getElementById('user_password').required = false;

        document.getElementById('user_role').value = user.role;
        document.getElementById('user_department').value = user.department_id || '';

        userModal.show();
    }

    function openDeleteModal(id, username) {
        document.getElementById('del_user_id').value = id;
        document.getElementById('del_username_display').textContent = username;
        deleteModal.show();
    }

    // --- VALIDACIÓN EXTRA JS AL ENVIAR ---
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const userId = document.getElementById('user_id').value;
        const username = document.getElementById('user_username').value.trim();
        const fullname = document.getElementById('user_fullname').value.trim();
        const dept = document.getElementById('user_department').value;
        const pass = document.getElementById('user_password').value;

        // 1. Validar campos vacíos
        if (!username || !fullname) {
            e.preventDefault();
            alert('Por favor, completa el Usuario y el Nombre Completo.');
            return;
        }

        // 2. Validar departamento (Opcional según tu lógica, aquí forzamos si es usuario)
        // if (dept === "") { e.preventDefault(); alert('Debes seleccionar un Departamento.'); return; }

        // 3. Validar contraseña (Solo si es nuevo)
        if ((userId === '0' || userId === '') && pass.trim() === '') {
            e.preventDefault();
            alert('La contraseña es obligatoria para un nuevo usuario.');
            return;
        }
    });
    </script>
    <?php include("footer.php"); ?>
</body>

</html>