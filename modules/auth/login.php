<?php
session_start();
require_once '../../conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuario WHERE correo='$correo' AND estado_cuenta='activo'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) === 1) {
        $usuario = mysqli_fetch_assoc($resultado);
        if (password_verify($contrasena, $usuario['contraseña'])) {
            $_SESSION['usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombres'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'administrador') {
                header("Location: ../../modules/gestion/panel.php");
            } else {
                header("Location: ../../modules/propiedades/listar.php");
            }
            exit();
        } else {
            $error = 'Contraseña incorrecta.';
        }
    } else {
        $error = 'Usuario no encontrado o cuenta inactiva.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%);
        }
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }
        .login-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(26,115,232,0.12);
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 8px;
        }
        .login-logo span {
            font-size: 40px;
        }
        .login-title {
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }
        .login-sub {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-bottom: 28px;
        }
        .login-box input {
            width: 100%;
            padding: 13px 16px;
            margin-bottom: 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }
        .login-box input:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.12);
            background: white;
        }
        .login-box button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #1a73e8, #0d47a1);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            font-family: 'Inter', Arial, sans-serif;
            margin-top: 4px;
        }
        .login-box button:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-footer a {
            color: #1a73e8;
            font-weight: 600;
            text-decoration: none;
        }
        .login-footer a:hover { text-decoration: underline; }
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 20px 0; color: #ccc; font-size: 13px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: #eee;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo"><span>🏠</span></div>
            <h2 class="login-title">Renta Fácil</h2>
            <p class="login-sub">Inicia sesión para continuar</p>

            <?php if ($error): ?>
                <div class="alerta error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="email" name="correo" placeholder="📧 Correo electrónico" required>
                <input type="password" name="contrasena" placeholder="🔒 Contraseña" required>
                <button type="submit">Ingresar →</button>
            </form>

            <div class="divider">o</div>

            <div class="login-footer">
                ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
            </div>
        </div>
    </div>
</body>
</html>