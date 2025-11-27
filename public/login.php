<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$err = '';
if($_POST){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    if($user && $user['password'] === $password){
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $err = "Usuario o contraseña incorrecta.";
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Sistema de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a7b 50%, #1a4d5e 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    /* Fondo geométrico animado */
    body::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background-image:
            linear-gradient(30deg, transparent 40%, rgba(72, 187, 173, 0.1) 40%, rgba(72, 187, 173, 0.1) 60%, transparent 60%),
            linear-gradient(150deg, transparent 40%, rgba(72, 187, 173, 0.08) 40%, rgba(72, 187, 173, 0.08) 60%, transparent 60%);
        background-size: 400px 400px;
        animation: geometricMove 20s linear infinite;
    }

    @keyframes geometricMove {
        0% {
            background-position: 0 0, 0 0;
        }

        100% {
            background-position: 400px 400px, -400px -400px;
        }
    }

    /* Círculos flotantes */
    .circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(72, 187, 173, 0.1);
        animation: float 15s infinite ease-in-out;
    }

    .circle:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .circle:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 70%;
        left: 80%;
        animation-delay: 2s;
    }

    .circle:nth-child(3) {
        width: 60px;
        height: 60px;
        top: 40%;
        left: 5%;
        animation-delay: 4s;
    }

    .circle:nth-child(4) {
        width: 100px;
        height: 100px;
        top: 20%;
        left: 85%;
        animation-delay: 1s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0) translateX(0);
        }

        25% {
            transform: translateY(-20px) translateX(10px);
        }

        50% {
            transform: translateY(-40px) translateX(-10px);
        }

        75% {
            transform: translateY(-20px) translateX(5px);
        }
    }

    .login-container {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 480px;
        padding: 20px;
    }

    .login-card {
        background: rgba(20, 42, 65, 0.85);
        backdrop-filter: blur(10px);
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(72, 187, 173, 0.2);
    }

    .login-header {
        background: linear-gradient(135deg, #48bbad 0%, #3da89c 100%);
        padding: 50px 40px 40px;
        text-align: center;
        position: relative;
    }

    .login-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.3);
    }

    .login-header h1 {
        color: #1a3a4d;
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 3px;
        margin: 0;
        text-transform: uppercase;
    }

    .login-body {
        padding: 50px 40px;
        background: rgba(30, 58, 95, 0.6);
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.15);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #ff6b6b;
        border-radius: 15px;
        padding: 12px 18px;
        margin-bottom: 25px;
        font-size: 0.9rem;
    }

    .input-group-custom {
        position: relative;
        margin-bottom: 30px;
    }

    .input-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #48bbad;
        font-size: 1.3rem;
        z-index: 5;
    }

    .form-control-custom {
        background: transparent;
        border: none;
        border-bottom: 2px solid rgba(72, 187, 173, 0.3);
        color: #fff;
        padding: 18px 20px 18px 60px;
        font-size: 1rem;
        width: 100%;
        transition: all 0.3s ease;
        border-radius: 0;
    }

    .form-control-custom:focus {
        outline: none;
        border-bottom-color: #48bbad;
        background: rgba(72, 187, 173, 0.05);
    }

    .form-control-custom::placeholder {
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.95rem;
    }

    .options-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        font-size: 0.85rem;
    }

    .remember-me {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.6);
    }

    .remember-me input[type="checkbox"] {
        margin-right: 8px;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .forgot-password {
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        font-style: italic;
        transition: color 0.3s ease;
    }

    .forgot-password:hover {
        color: #48bbad;
    }

    .btn-login {
        background: linear-gradient(135deg, #48bbad 0%, #3da89c 100%);
        border: none;
        color: #1a3a4d;
        font-weight: 700;
        font-size: 1.1rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 18px;
        width: 100%;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(72, 187, 173, 0.3);
    }

    .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(72, 187, 173, 0.4);
        background: linear-gradient(135deg, #5ccfc0 0%, #48bbad 100%);
    }

    .btn-login:active {
        transform: translateY(-1px);
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .login-card {
        animation: fadeInScale 0.6s ease;
    }

    @media (max-width: 576px) {
        .login-header h1 {
            font-size: 1.4rem;
            letter-spacing: 2px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .options-row {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
    }
    </style>
</head>

<body>
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Sistema de Inventario</h1>
            </div>

            <div class="login-body">
                <?php if($err): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($err) ?>
                </div>
                <?php endif; ?>

                <form method="post">
                    <div class="input-group-custom">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="text" name="username" class="form-control-custom" placeholder="Usuario" required
                            autofocus>
                    </div>

                    <div class="input-group-custom">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control-custom" placeholder="Contraseña"
                            required>
                    </div>


                    <button type="submit" class="btn-login">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>