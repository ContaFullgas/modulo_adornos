<?php 
include("../config/auth.php"); 
require_login(); 
require_once __DIR__ . '/temas.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tema['titulo_pestana']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
    :root {
        /* Variables dinámicas del tema activo */
        --bg-body: <?=$tema['bg_body'] ?>;
        --text-dark: <?=$tema['text_dark'] ?>;
        --text-light: #ecfdf5;

        --primary-color: <?=$tema['primary_color'] ?>;
        --dark-color: <?=$tema['dark_color'] ?>;
        --accent-color: <?=$tema['accent_color'] ?>;

        --card-surface: #ffffff;
        --card-border: #f0fdf4;

        --dot-color: <?=$tema['dot_color'] ?>;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: var(--bg-body);
        min-height: 100vh;
        font-family: 'Outfit', sans-serif;
        color: var(--text-dark);
        /* Patrón de fondo dinámico */
        background-image: radial-gradient(var(--dot-color) 0.5px, transparent 0.5px);
        background-size: 20px 20px;
    }

    /* Contenedor principal */
    .container-dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        padding-top: 130px;
        position: relative;
        z-index: 2;
    }

    /* Partículas dinámicas (nieve/corazones/etc) */
    .celebration-particle {
        position: fixed;
        top: -10px;
        z-index: 1;
        color: var(--dot-color);
        font-size: 1em;
        opacity: 0.6;
        animation: fall linear infinite;
        pointer-events: none;
    }

    @keyframes fall {
        to {
            transform: translateY(105vh) rotate(360deg);
        }
    }

    /* --- HERO SECTION DINÁMICO --- */
    .hero-card {
        background: <?=$tema['hero_gradient'] ?>;
        border-radius: 30px;
        padding: 4rem 3rem;
        margin-bottom: 3.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Círculos decorativos en el fondo */
    .hero-card::before,
    .hero-card::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .hero-card::before {
        width: 300px;
        height: 300px;
        top: -100px;
        right: -50px;
    }

    .hero-card::after {
        width: 200px;
        height: 200px;
        bottom: -50px;
        left: 50px;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 600px;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        line-height: 1.1;
        color: #ffffff;
    }

    .hero-highlight {
        color: var(--accent-color);
        filter: brightness(1.3);
        text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        font-weight: 300;
    }

    /* Icono gigante decorativo dinámico */
    .hero-icon-bg {
        font-size: 10rem;
        color: rgba(255, 255, 255, 0.1);
        position: absolute;
        right: 50px;
        top: 50%;
        transform: translateY(-50%) rotate(15deg);
    }

    /* --- GRID DE TARJETAS --- */
    .cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .action-card {
        background: var(--card-surface);
        border: 1px solid var(--card-border);
        border-radius: 24px;
        padding: 2.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Borde superior de color dinámico */
    .card-primary {
        border-top: 6px solid var(--primary-color);
    }

    .card-accent {
        border-top: 6px solid var(--accent-color);
    }

    .action-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.08);
    }

    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
    }

    .card-primary .card-icon {
        background: color-mix(in srgb, var(--primary-color) 15%, white);
        color: var(--primary-color);
    }

    .card-accent .card-icon {
        background: color-mix(in srgb, var(--accent-color) 15%, white);
        color: var(--accent-color);
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .card-text {
        color: #4b5563;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    /* Botones Dinámicos */
    .btn-elegant {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.8rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-primary-theme {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 15px color-mix(in srgb, var(--primary-color) 30%, transparent);
    }

    .btn-primary-theme:hover {
        background: var(--dark-color);
        color: white;
        transform: scale(1.02);
    }

    .btn-accent-theme {
        background: var(--accent-color);
        color: white;
        box-shadow: 0 4px 15px color-mix(in srgb, var(--accent-color) 30%, transparent);
    }

    .btn-accent-theme:hover {
        background: color-mix(in srgb, var(--accent-color) 80%, black);
        color: white;
        transform: scale(1.02);
    }

    @media (max-width: 768px) {
        .hero-card {
            padding: 3rem 1.5rem;
            flex-direction: column;
            text-align: center;
        }

        .hero-icon-bg {
            display: none;
        }

        .container-dashboard {
            padding-top: 110px;
        }
    }
    </style>
</head>

<body>

    <?php include("navbar.php"); ?>

    <div id="celebration-particles"></div>

    <div class="container-dashboard">

        <!-- HERO DINÁMICO -->
        <div class="hero-card">
            <div class="hero-content">
                <h1 class="hero-title">
                    <?= $tema['hero_title'] ?>
                </h1>
                <p class="hero-subtitle">
                    <?= htmlspecialchars($tema['hero_subtitle']) ?>
                </p>
            </div>
            <i class="fas <?= $tema['hero_icon'] ?> hero-icon-bg"></i>
        </div>

        <div class="cards-container">

            <!-- CARD 1: Inventario (Adornos) -->
            <div class="action-card card-primary">
                <div>
                    <div class="card-icon">
                        <i class="fas <?= $tema['icon_adornos'] ?>"></i>
                    </div>
                    <h2 class="card-title">Inventario</h2>
                    <p class="card-text">
                        Administra el catálogo de artículos. Visualiza existencias, agrega novedades y mantén tu
                        colección al día.
                    </p>
                </div>
                <a href="items.php" class="btn-elegant btn-primary-theme">
                    Ver Artículos <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

            <!-- CARD 2: Reservas -->
            <div class="action-card card-accent">
                <div>
                    <div class="card-icon">
                        <i class="fas <?= $tema['icon_reservas'] ?>"></i>
                    </div>
                    <h2 class="card-title">Reservas</h2>
                    <p class="card-text">
                        Control total sobre los apartados. Revisa quién ha solicitado material y gestiona las
                        devoluciones fácilmente.
                    </p>
                </div>
                <a href="reservations.php" class="btn-elegant btn-accent-theme">
                    Gestionar Reservas <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

        </div>
    </div>

    <script>
    // Crear partículas dinámicas según el tema activo
    function createCelebrationParticles() {
        const container = document.getElementById('celebration-particles');

        // Símbolos dinámicos desde PHP
        const symbols = <?= json_encode($tema['particle_symbols']) ?>;

        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.classList.add('celebration-particle');
            particle.innerHTML = symbols[Math.floor(Math.random() * symbols.length)];
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 5 + 8) + 's';
            particle.style.animationDelay = Math.random() * 5 + 's';
            particle.style.fontSize = (Math.random() * 15 + 10) + 'px';
            container.appendChild(particle);
        }
    }
    createCelebrationParticles();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("footer.php"); ?>
</body>

</html>