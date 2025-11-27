<?php include("../config/auth.php"); require_login(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navidad - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
    :root {
        /* Paleta Verde & Dorado */
        --bg-body: #fdfcf8;
        /* Crema muy suave */
        --text-dark: #064e3b;
        /* Verde muy oscuro para textos */
        --text-light: #ecfdf5;

        --primary-green: #059669;
        /* Esmeralda Vibrante */
        --dark-green: #065f46;
        /* Pino Profundo */

        --accent-gold: #d97706;
        /* Dorado */
        --accent-gold-light: #fcd34d;

        --card-surface: #ffffff;
        --card-border: #f0fdf4;
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
        /* Patrón de fondo muy sutil */
        background-image: radial-gradient(#d1fae5 0.5px, transparent 0.5px);
        background-size: 20px 20px;
    }

    /* Contenedor principal */
    .container-dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        padding-top: 130px;
        /* Espacio para el Navbar */
        position: relative;
        z-index: 2;
    }

    /* Partículas de nieve (Dorado suave) */
    .snowflake {
        position: fixed;
        top: -10px;
        z-index: 1;
        color: #fbbf24;
        /* Copos dorados */
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

    /* --- HERO SECTION --- */
    .hero-card {
        background: linear-gradient(135deg, var(--dark-green) 0%, var(--primary-green) 100%);
        border-radius: 30px;
        padding: 4rem 3rem;
        margin-bottom: 3.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px -10px rgba(6, 95, 70, 0.4);
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
        color: var(--accent-gold-light);
        /* Texto dorado */
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        font-weight: 300;
    }

    /* Icono gigante decorativo */
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

    /* Borde superior de color */
    .action-card::top {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
    }

    .card-green {
        border-top: 6px solid var(--primary-green);
    }

    .card-gold {
        border-top: 6px solid var(--accent-gold);
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

    .card-green .card-icon {
        background: #ecfdf5;
        color: var(--primary-green);
    }

    .card-gold .card-icon {
        background: #fffbeb;
        color: var(--accent-gold);
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

    /* Botones Elegantes */
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

    .btn-green {
        background: var(--primary-green);
        color: white;
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }

    .btn-green:hover {
        background: var(--dark-green);
        color: white;
        transform: scale(1.02);
    }

    .btn-gold {
        background: var(--accent-gold);
        color: white;
        box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
    }

    .btn-gold:hover {
        background: #b45309;
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

    <div id="gold-snow"></div>

    <div class="container-dashboard">

        <div class="hero-card">
            <div class="hero-content">
                <h1 class="hero-title">
                    Decora tu mundo a <span class="hero-highlight">tu manera</span>
                </h1>
                <p class="hero-subtitle">
                    Selecciona tus decoraciones favoritas
                </p>
            </div>
            <i class="fas fa-tree hero-icon-bg"></i>
        </div>

        <div class="cards-container">

            <div class="action-card card-green">
                <div>
                    <div class="card-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h2 class="card-title">Inventario</h2>
                    <p class="card-text">
                        Administra el catálogo de adornos. Visualiza existencias, agrega novedades y mantén tu colección
                        al día.
                    </p>
                </div>
                <a href="items.php" class="btn-elegant btn-green">
                    Ver Adornos <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

            <div class="action-card card-gold">
                <div>
                    <div class="card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h2 class="card-title">Reservas</h2>
                    <p class="card-text">
                        Control total sobre los apartados. Revisa quién ha solicitado material y gestiona las
                        devoluciones fácilmente.
                    </p>
                </div>
                <a href="reservations.php" class="btn-elegant btn-gold">
                    Gestionar Reservas <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

        </div>
    </div>

    <script>
    // Efecto de nieve dorada
    function createGoldSnow() {
        const container = document.getElementById('gold-snow');
        const symbols = ['✦', '•', '⋆'];

        for (let i = 0; i < 30; i++) {
            const flake = document.createElement('div');
            flake.classList.add('snowflake');
            flake.innerHTML = symbols[Math.floor(Math.random() * symbols.length)];
            flake.style.left = Math.random() * 100 + '%';
            flake.style.animationDuration = (Math.random() * 5 + 8) + 's';
            flake.style.animationDelay = Math.random() * 5 + 's';
            flake.style.fontSize = (Math.random() * 15 + 10) + 'px';
            container.appendChild(flake);
        }
    }
    createGoldSnow();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("footer.php"); ?>
</body>

</html>