<?php
// Si $tema no existe, cargar valores por defecto
if (!isset($tema)) {
    $tema = [
        'primary_color' => '#334155',
        'dark_color' => '#1e293b',
        'accent_color' => '#2563eb'
    ];
}
?>

<footer class="footer-custom mt-auto">
    <div class="container text-center">
        <p class="mb-1">
            &copy; <?php echo date('Y'); ?> <strong>Sistema de Inventario</strong>. Todos los derechos reservados.
        </p>
        <div class="footer-divider"></div>
        <p class="mb-0 small credits-text">
            <i class="fas fa-code me-1"></i> Desarrollado por <strong>Contabilidad - Sistemas</strong>
        </p>
    </div>
</footer>

<style>
/* REPARACIÓN PRINCIPAL: Forzar al body a ocupar toda la pantalla */
html,
body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    /* Asegura que el cuerpo ocupe el alto de la ventana */
}

/* Footer Dinámico */
.footer-custom {
    background: linear-gradient(90deg,
            <?=$tema['primary_color'] ?> 0%,
            <?=$tema['dark_color'] ?> 100%);
    color: #e2e8f0;
    padding: 2rem 0;

    /* CAMBIO CLAVE: margin-top: auto empuja el footer al fondo en contenedores flex */
    margin-top: auto !important;

    border-top: 3px solid <?=$tema['accent_color'] ?>;
    position: relative;
    z-index: 10;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.2);
    width: 100%;
}

.footer-custom strong {
    color: #ffffff;
}

.footer-divider {
    width: 50px;
    height: 2px;
    background-color: <?=$tema['accent_color'] ?>;
    margin: 0.8rem auto;
    opacity: 0.7;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.credits-text {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.credits-text strong {
    color: <?=$tema['accent_color'] ?>;
    filter: brightness(1.2);
}

.credits-text i {
    color: <?=$tema['accent_color'] ?>;
}

.footer-custom:hover .footer-divider {
    width: 80px;
    opacity: 1;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-custom {
        padding: 1.5rem 1rem;
    }
}
</style>