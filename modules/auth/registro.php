<?php
session_start();
require_once '../../conexion.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
    $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $tipo_documento = mysqli_real_escape_string($conexion, $_POST['tipo_documento']);
    $numero_documento = mysqli_real_escape_string($conexion, $_POST['numero_documento']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol = mysqli_real_escape_string($conexion, $_POST['rol']);

    $verificar = mysqli_query($conexion, "SELECT id_usuario FROM usuario WHERE correo='$correo' OR numero_documento='$numero_documento'");

    if (mysqli_num_rows($verificar) > 0) {
        $error = 'El correo o documento ya está registrado.';
    } else {
        $sql = "INSERT INTO usuario (nombres, apellidos, correo, telefono, tipo_documento, numero_documento, contraseña, rol, estado_cuenta)
                VALUES ('$nombres','$apellidos','$correo','$telefono','$tipo_documento','$numero_documento','$contrasena','$rol','activo')";
        if (mysqli_query($conexion, $sql)) {
            $exito = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
        } else {
            $error = 'Error al registrar: ' . mysqli_error($conexion);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%);
            display: flex;
            flex-direction: column;
        }
        .registro-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }
        .registro-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(26,115,232,0.12);
            padding: 40px;
            width: 100%;
            max-width: 520px;
        }
        .registro-logo {
            text-align: center;
            font-size: 36px;
            margin-bottom: 8px;
        }
        .registro-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .registro-sub {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-bottom: 28px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .registro-box input,
        .registro-box select {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
            color: #333;
        }
        .registro-box input:focus,
        .registro-box select:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.12);
            background: white;
        }
        .rol-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }
        .rol-option {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafafa;
        }
        .rol-option:hover { border-color: #1a73e8; background: #e8f0fe; }
        .rol-option input[type="radio"] { display: none; }
        .rol-option.selected { border-color: #1a73e8; background: #e8f0fe; }
        .rol-option .rol-icon { font-size: 28px; margin-bottom: 6px; }
        .rol-option .rol-name { font-size: 14px; font-weight: 600; color: #333; }
        .rol-option .rol-desc { font-size: 12px; color: #888; margin-top: 2px; }
        .registro-box button {
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
        .registro-box button:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }
        .registro-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .registro-footer a {
            color: #1a73e8;
            font-weight: 600;
            text-decoration: none;
        }
        .registro-footer a:hover { text-decoration: underline; }
        .section-label {
            font-size: 12px;
            font-weight: 600;
            color: #1a73e8;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 10px;
            margin-top: 4px;
        }
        @media(max-width:480px) {
            .form-row { grid-template-columns: 1fr; }
            .registro-box { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="registro-wrapper">
        <div class="registro-box">
            <div class="registro-logo">🏠</div>
            <h2 class="registro-title">Crear cuenta</h2>
            <p class="registro-sub">Únete a Renta Fácil gratis</p>

            <?php if ($error): ?>
                <div class="alerta error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="alerta exito"><?= $exito ?> <a href="login.php">Ir al login</a></div>
            <?php endif; ?>

            <form method="POST">
                <p class="section-label">Información personal</p>
                <div class="form-row">
                    <input type="text" name="nombres" placeholder="Nombres" required>
                    <input type="text" name="apellidos" placeholder="Apellidos" required>
                </div>
                <input type="email" name="correo" placeholder="📧 Correo electrónico" required>
                <input type="tel" name="telefono" placeholder="📱 Teléfono">

                <p class="section-label">Documento</p>
                <div class="form-row">
                    <select name="tipo_documento" required>
                        <option value="">Tipo de documento</option>
                        <option value="CC">Cédula de ciudadanía</option>
                        <option value="TI">Tarjeta de identidad</option>
                        <option value="CE">Cédula de extranjería</option>
                    </select>
                    <input type="text" name="numero_documento" placeholder="Número" required>
                </div>

                <p class="section-label">Seguridad</p>
                <input type="password" name="contrasena" placeholder="🔒 Contraseña" required>

                <p class="section-label">¿Cómo vas a usar Renta Fácil?</p>
                <div class="rol-selector">
                    <label class="rol-option" id="opt-arrendador">
                        <input type="radio" name="rol" value="arrendador" required onclick="selectRol('arrendador')">
                        <div class="rol-icon">🏠</div>
                        <div class="rol-name">Arrendador</div>
                        <div class="rol-desc">Quiero publicar propiedades</div>
                    </label>
                    <label class="rol-option" id="opt-arrendatario">
                        <input type="radio" name="rol" value="arrendatario" onclick="selectRol('arrendatario')">
                        <div class="rol-icon">🔍</div>
                        <div class="rol-name">Arrendatario</div>
                        <div class="rol-desc">Quiero encontrar un lugar</div>
                    </label>
                </div>

                <button type="submit">Crear cuenta →</button>
            </form>

            <div class="registro-footer">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </div>
        </div>
    </div>

    <script>
        function selectRol(rol) {
            document.getElementById('opt-arrendador').classList.remove('selected');
            document.getElementById('opt-arrendatario').classList.remove('selected');
            document.getElementById('opt-' + rol).classList.add('selected');
        }
    </script>
</body>
</html>