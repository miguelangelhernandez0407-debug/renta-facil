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
</head>
<body>
    <div class="contenedor-form">
        <h2>Iniciar sesión en Renta Fácil</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
</body>
</html>