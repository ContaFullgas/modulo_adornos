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
/* Estilos del Footer para que combine con el Navbar */
.footer-custom {
    /* Mismo gradiente que el Navbar */
    background: linear-gradient(90deg, #022c22 0%, #14532d 100%);
    color: #e2e8f0;
    /* Texto gris muy claro */
    padding: 2rem 0;
    margin-top: 4rem !important;
    /* Separación del contenido de arriba */
    border-top: 3px solid #d97706;
    /* Línea dorada superior */
    position: relative;
    z-index: 10;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.2);
}

.footer-custom strong {
    color: #ffffff;
}

/* Pequeña línea divisoria decorativa */
.footer-divider {
    width: 50px;
    height: 2px;
    background-color: #fbbf24;
    /* Dorado */
    margin: 0.8rem auto;
    opacity: 0.5;
    border-radius: 2px;
}

.credits-text {
    color: #86efac;
    /* Verde claro sutil para el departamento */
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    opacity: 0.8;
}

.credits-text strong {
    color: #fbbf24;
    /* Dorado para el nombre del depto */
    font-weight: 700;
}
</style>