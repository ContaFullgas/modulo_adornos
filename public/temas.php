<?php
// temas.php

// Intentamos usar la conexión existente ($conn)
if (!isset($conn)) {
    global $conn;
}

// ==========================================
// 1. CONFIGURACIÓN NEUTRAL (Por defecto)
// ==========================================
// Cambio: Dejamos el morado y usamos tonos "Slate" (Gris profesional) y Azul Acero.
$tema = [
    'nav_title'      => 'Gestión',
    'titulo_pestana' => 'Dashboard',
    
    // Paleta de Colores "Clean Corporate"
    'bg_body'        => '#f8fafc', // Fondo gris muy claro (casi blanco)
    'text_dark'      => '#0f172a', // Texto casi negro
    'primary_color'  => '#334155', // Gris Azulado (Slate 700) - Elegante y sobrio
    'dark_color'     => '#1e293b', // Gris Oscuro (Slate 800)
    'accent_color'   => '#2563eb', // Azul Royal (para botones y detalles importantes)
    
    // Gradiente del Banner (Gris oscuro profesional)
    'hero_gradient'  => 'linear-gradient(135deg, #1e293b 0%, #334155 100%)',
    
    'hero_title'     => 'Bienvenido a tu <span class="hero-highlight">Espacio</span>',
    'hero_subtitle'  => 'Gestiona tu inventario y recursos eficientemente',
    
    // Iconos Genéricos (Cajas y Listas)
    'hero_icon'      => 'fa-layer-group',
    'icon_adornos'   => 'fa-boxes-stacked',
    'icon_reservas'  => 'fa-clipboard-list',
    
    'dot_color'      => '#cbd5e1', // Puntos de fondo gris suave
    'particle_symbols' => ['•', '◦', '▪'] // Formas geométricas simples
];

// ==========================================
// 2. CONSULTAR CELEBRACIÓN ACTIVA
// ==========================================
if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT name FROM celebrations WHERE is_active = 1 LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre_festividad = mb_strtolower($row['name'], 'UTF-8');

        // --- LÓGICA DE SELECCIÓN DE TEMA ---

        // A) HALLOWEEN / DÍA DE MUERTOS
        if (strpos($nombre_festividad, 'halloween') !== false || strpos($nombre_festividad, 'muertos') !== false || strpos($nombre_festividad, 'brujas') !== false) {
            $tema = [
                'nav_title'      => 'Halloween',
                'titulo_pestana' => 'Halloween - Dashboard',
                'bg_body'        => '#fff7ed', // Fondo crema naranja muy suave
                'text_dark'      => '#292524', // Negro cálido
                'primary_color'  => '#ea580c', // Naranja Calabaza
                'dark_color'     => '#1c1917', // Casi negro
                'accent_color'   => '#9333ea', // Morado místico (para contraste)
                'hero_gradient'  => 'linear-gradient(135deg, #451a03 0%, #ea580c 100%)', // Café oscuro a Naranja
                'hero_title'     => 'Dulce o <span class="hero-highlight">Truco</span>',
                'hero_subtitle'  => 'Prepara el inventario más espeluznante',
                'hero_icon'      => 'fa-ghost',
                'icon_adornos'   => 'fa-mask', // Máscara o disfraz
                'icon_reservas'  => 'fa-spider', // Araña
                'dot_color'      => '#fdba74', // Puntos naranjas
                'particle_symbols' => ['🎃', '👻', '🕸️', '🍬'] // Emojis de Halloween
            ];
        }

        // B) MES PATRIO (16 DE SEPTIEMBRE - MÉXICO)
        elseif (strpos($nombre_festividad, 'independencia') !== false || strpos($nombre_festividad, 'patria') !== false || strpos($nombre_festividad, 'mexico') !== false || strpos($nombre_festividad, 'septiembre') !== false) {
            $tema = [
                'nav_title'      => 'Mes Patrio',
                'titulo_pestana' => 'Viva México - Dashboard',
                'bg_body'        => '#f0fdf4', // Fondo verde muy pálido
                'text_dark'      => '#14532d', // Verde muy oscuro
                'primary_color'  => '#15803d', // Verde Bandera
                'dark_color'     => '#b91c1c', // Rojo Bandera (para contrastes fuertes)
                'accent_color'   => '#d97706', // Dorado (para el águila/detalles)
                'hero_gradient'  => 'linear-gradient(135deg, #15803d 0%, #047857 50%, #b91c1c 100%)', // Gradiente tricolor sutil
                'hero_title'     => '¡Viva <span class="hero-highlight">México!</span>',
                'hero_subtitle'  => 'Gestiona las fiestas patrias con orgullo',
                'hero_icon'      => 'fa-flag', // Bandera
                'icon_adornos'   => 'fa-guitar', // Mariachi/Fiesta
                'icon_reservas'  => 'fa-pepper-hot', // Chile (o podría ser fa-landmark)
                'dot_color'      => '#86efac', // Puntos verdes
                'particle_symbols' => ['🇲🇽', '🌵', '🎆', '💚'] 
            ];
        }

        // C) SAN VALENTÍN
        elseif (strpos($nombre_festividad, 'valentin') !== false || strpos($nombre_festividad, 'amor') !== false || strpos($nombre_festividad, 'amistad') !== false) {
            $tema = [
                'nav_title'      => 'San Valentín',
                'titulo_pestana' => 'San Valentín - Dashboard',
                'bg_body'        => '#fff0f3',
                'text_dark'      => '#590d22',
                'primary_color'  => '#e63946', // Rojo
                'dark_color'     => '#800f2f', // Vino
                'accent_color'   => '#ff4d6d', // Rosa
                'hero_gradient'  => 'linear-gradient(135deg, #800f2f 0%, #e63946 100%)',
                'hero_title'     => 'Celebra el amor a <span class="hero-highlight">tu manera</span>',
                'hero_subtitle'  => 'Prepara los detalles más especiales',
                'hero_icon'      => 'fa-heart',
                'icon_adornos'   => 'fa-gift',
                'icon_reservas'  => 'fa-envelope-open-text',
                'dot_color'      => '#ffb3c1',
                'particle_symbols' => ['❤', '♥', '❣']
            ];
        }

        // D) NAVIDAD
        elseif (strpos($nombre_festividad, 'navidad') !== false || strpos($nombre_festividad, 'nochebuena') !== false) {
            $tema = [
                'nav_title'      => 'Navidad',
                'titulo_pestana' => 'Navidad - Dashboard',
                'bg_body'        => '#fdfcf8',
                'text_dark'      => '#064e3b',
                'primary_color'  => '#059669', // Verde Esmeralda
                'dark_color'     => '#022c22', // Pino
                'accent_color'   => '#d97706', // Dorado
                'hero_gradient'  => 'linear-gradient(135deg, #022c22 0%, #059669 100%)',
                'hero_title'     => 'Decora tu mundo a <span class="hero-highlight">tu manera</span>',
                'hero_subtitle'  => 'Selecciona tus decoraciones favoritas',
                'hero_icon'      => 'fa-tree',
                'icon_adornos'   => 'fa-sleigh',
                'icon_reservas'  => 'fa-star',
                'dot_color'      => '#d1fae5',
                'particle_symbols' => ['✦', '•', '⋆']
            ];
        }
    }
}
?>