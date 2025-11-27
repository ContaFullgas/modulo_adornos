<?php
require_once __DIR__ . '/../config/auth.php';
$user = is_logged_in() ? current_user() : null;
?>
<style>
/* === NAVBAR ESTILO "NOCHE DE PAZ" (Dark Forest & Gold) === */
.navbar-custom {
    background: linear-gradient(90deg, #022c22 0%, #14532d 100%);
    padding: 0.8rem 0;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.25);
    border-bottom: 2px solid #d97706;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    min-height: 80px;
}

/* LOGO */
.navbar-brand-custom {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    margin-right: 1.5rem;
}

.navbar-brand-custom i {
    font-size: 1.8rem;
    color: #f4f1ebff;
    filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.4));
}

/* ENLACES */
.nav-link-custom {
    color: #e2e8f0 !important;
    font-weight: 500;
    padding: 0.6rem 1rem !important;
    margin: 0 2px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    white-space: nowrap;
}

.nav-link-custom:hover,
.nav-link-custom.active,
.nav-link-custom[aria-expanded="true"] {
    color: #f4f1ebff !important;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
}

.nav-link-custom i {
    color: #f4f1ebff
}

.nav-link-custom:hover i {
    color: #f4f1ebff;
}

/* DROPDOWN MENU */
.dropdown-menu-dark-custom {
    background-color: #064e3b;
    /* Fondo verde oscuro */
    border: 1px solid #d97706;
    /* Borde dorado */
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    padding: 0.5rem;
    margin-top: 10px;
}

.dropdown-item-custom {
    color: #e2e8f0;
    padding: 0.7rem 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}

.dropdown-item-custom:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #f4f1ebff;
    transform: translateX(5px);
}

.dropdown-item-custom i {
    width: 20px;
    text-align: center;
    color: #f4f1ebff;
}

/* SECCIÓN USUARIO */
.user-section {
    display: flex;
    align-items: center;
    gap: 15px;
    padding-left: 20px;
    margin-left: 10px;
    border-left: 1px solid rgba(255, 255, 255, 0.15);
    height: 40px;
}

.user-welcome {
    color: #ffffff;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.5rem 1rem;
    background: rgba(0, 0, 0, 0.25);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    white-space: nowrap;
}

.user-welcome i {
    color: #f4f1ebff;
}

.badge-admin {
    background: #d97706;
    color: #fff;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 0.7rem;
    margin-left: 5px;
}

/* BOTONES */
.btn-logout {
    background: #831818ff;
    color: #fbfbfbff;
    border: 1px solid rgba(239, 68, 68, 0.4);
    padding: 0.5rem 1.2rem;
    border-radius: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-login {
    background: #d97706;
    color: #fff;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-login:hover {
    background: #b45309;
    color: white;
}

/* MOVIL */
.navbar-toggler-custom {
    border: 1px solid #f4f1ebff;
    padding: 5px 10px;
}

.navbar-toggler-icon-custom {
    background-color: #f4f1ebff;
    height: 2px;
    width: 22px;
    display: block;
    position: relative;
}

.navbar-toggler-icon-custom::before,
.navbar-toggler-icon-custom::after {
    background-color: #f4f1ebff;
    height: 2px;
    width: 22px;
    position: absolute;
    content: '';
    left: 0;
}

.navbar-toggler-icon-custom::before {
    top: -6px;
}

.navbar-toggler-icon-custom::after {
    top: 6px;
}

@media (max-width: 991px) {
    .navbar-collapse {
        background: #022c22;
        margin-top: 15px;
        padding: 1rem;
        border-radius: 10px;
        border: 1px solid rgba(251, 191, 36, 0.2);
    }

    .user-section {
        border-left: none;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-left: 0;
        margin-left: 0;
        margin-top: 1rem;
        padding-top: 1rem;
        height: auto;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .nav-link-custom {
        width: 100%;
    }

    .user-welcome,
    .btn-logout {
        width: 100%;
        justify-content: center;
    }
}
</style>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid px-4">
        <a class="navbar-brand-custom" href="index.php">
            <i class="fas fa-tree"></i>
            <span>Navidad</span>
        </a>

        <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon-custom"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link-custom" href="items.php">
                        <i class="fas fa-gifts"></i><span>Adornos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link-custom" href="reservations.php">
                        <i class="fas fa-bookmark"></i><span>Reservas</span>
                    </a>
                </li>

                <?php if($user && $user['role'] === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link-custom dropdown-toggle" href="#" id="adminDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-shield"></i>
                        <span>Administración</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark-custom" aria-labelledby="adminDropdown">
                        <li>
                            <a class="dropdown-item-custom" href="departments.php">
                                <i class="fas fa-sitemap"></i> Departamentos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item-custom" href="celebrations.php">
                                <i class="fas fa-glass-cheers"></i> Celebraciones
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item-custom" href="admin_users.php">
                                <i class="fas fa-users"></i> Usuarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item-custom" href="returns.php">
                                <i class="fas fa-arrow-rotate-left"></i> Devoluciones
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if($user && $user['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link-custom" href="report_pdf.php?type=reservations" target="_blank">
                        <i class="fas fa-file-pdf"></i><span>PDF General</span>
                    </a>
                </li>
                <?php elseif($user && !empty($user['department_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link-custom"
                        href="report_pdf.php?type=reservations&dept=<?= (int)$user['department_id'] ?>" target="_blank">
                        <i class="fas fa-file-pdf"></i><span>Mi Reporte</span>
                    </a>
                </li>
                <?php endif; ?>

            </ul>

            <div class="user-section">
                <?php if($user): ?>
                <div class="user-welcome">
                    <i class="fas fa-circle-user"></i>
                    <span>Hola, <?= htmlspecialchars($user['username']) ?></span>
                    <?php if($user['role'] === 'admin'): ?>
                    <span class="badge-admin">ADMIN</span>
                    <?php endif; ?>
                </div>
                <a class="btn-logout" href="logout.php">
                    <i class="fas fa-power-off"></i>
                    <span>Cerrar Sesión</span>
                </a>
                <?php else: ?>
                <a class="btn-login" href="login.php">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    <span>Iniciar Sesión</span>
                </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</nav>