<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$exito = '';
$id_usuario = $_SESSION['usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
    $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    $usuario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM usuario WHERE id_usuario=$id_usuario"));

    if (!empty($contrasena_actual)) {
        if (!password_verify($contrasena_actual, $usuario['contraseña'])) {
            $error = 'La contraseña actual es incorrecta.';
        } elseif (empty($nueva_contrasena)) {
            $error = 'Debes ingresar la nueva contraseña.';
        } else {
            $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            mysqli_query($conexion, "UPDATE usuario SET nombres='$nombres', apellidos='$apellidos', telefono='$telefono', contraseña='$hash' WHERE id_usuario=$id_usuario");
            $exito = 'Perfil y contraseña actualizados correctamente.';
        }
    } else {
        mysqli_query($conexion, "UPDATE usuario SET nombres='$nombres', apellidos='$apellidos', telefono='$telefono' WHERE id_usuario=$id_usuario");
        $exito = 'Perfil actualizado correctamente.';
    }

    $_SESSION['nombre'] = $nombres;
}

$usuario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM usuario WHERE id_usuario=$id_usuario"));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor-form" style="max-width:540px">
        <h2>Mi perfil</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <div style="background:#e8f0fe;border-radius:8px;padding:12px;margin-bottom:20px">
            <p style="font-size:13px;color:#1a73e8"><strong>Rol:</strong> <?= ucfirst($usuario['rol']) ?></p>
            <p style="font-size:13px;color:#1a73e8"><strong>Correo:</strong> <?= $usuario['correo'] ?></p>
            <p style="font-size:13px;color:#1a73e8"><strong>Documento:</strong> <?= $usuario['tipo_documento'] ?>: <?= $usuario['numero_documento'] ?></p>
            <p style="font-size:13px;color:#1a73e8"><strong>Miembro desde:</strong> <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></p>
        </div>

        <form method="POST">
            <label style="font-size:13px;color:#666">Nombres</label>
            <input type="text" name="nombres" value="<?= $usuario['nombres'] ?>" required>

            <label style="font-size:13px;color:#666">Apellidos</label>
            <input type="text" name="apellidos" value="<?= $usuario['apellidos'] ?>" required>

            <label style="font-size:13px;color:#666">Teléfono</label>
            <input type="tel" name="telefono" value="<?= $usuario['telefono'] ?>">

            <hr style="margin:16px 0;border:none;border-top:1px solid #eee">
            <p style="font-size:13px;color:#666;margin-bottom:12px">Cambiar contraseña (opcional)</p>

            <label style="font-size:13px;color:#666">Contraseña actual</label>
            <input type="password" name="contrasena_actual" placeholder="Deja vacío si no quieres cambiarla">

            <label style="font-size:13px;color:#666">Nueva contraseña</label>
            <input type="password" name="nueva_contrasena" placeholder="Nueva contraseña">

            <button type="submit">Guardar cambios</button>
        </form>
    </div>
</body>
</html>