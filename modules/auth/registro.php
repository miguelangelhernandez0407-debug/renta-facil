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
            $exito = 'Usuario registrado correctamente. Ya puedes iniciar sesión.';
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
</head>
<body>
    <div class="contenedor-form">
        <h2>Crear cuenta en Renta Fácil</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="nombres" placeholder="Nombres" required>
            <input type="text" name="apellidos" placeholder="Apellidos" required>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <input type="tel" name="telefono" placeholder="Teléfono">
            <select name="tipo_documento" required>
                <option value="">Tipo de documento</option>
                <option value="CC">Cédula de ciudadanía</option>
                <option value="TI">Tarjeta de identidad</option>
                <option value="CE">Cédula de extranjería</option>
            </select>
            <input type="text" name="numero_documento" placeholder="Número de documento" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <select name="rol" required>
                <option value="">Selecciona tu rol</option>
                <option value="arrendador">Arrendador</option>
                <option value="arrendatario">Arrendatario</option>
            </select>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>