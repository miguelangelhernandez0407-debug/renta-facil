<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['suspender'])) {
    $id = intval($_GET['suspender']);
    mysqli_query($conexion, "UPDATE usuario SET estado_cuenta='suspendido' WHERE id_usuario=$id");
}

if (isset($_GET['activar'])) {
    $id = intval($_GET['activar']);
    mysqli_query($conexion, "UPDATE usuario SET estado_cuenta='activo' WHERE id_usuario=$id");
}

$usuarios = mysqli_query($conexion, "SELECT * FROM usuario ORDER BY fecha_registro DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor">
        <h2 style="margin-bottom:20px">Gestión de usuarios</h2>

        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Documento</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                <tr>
                    <td><?= $u['id_usuario'] ?></td>
                    <td><?= $u['nombres'] ?> <?= $u['apellidos'] ?></td>
                    <td><?= $u['correo'] ?></td>
                    <td><?= $u['tipo_documento'] ?>: <?= $u['numero_documento'] ?></td>
                    <td><?= ucfirst($u['rol']) ?></td>
                    <td>
                        <?php if ($u['estado_cuenta'] === 'activo'): ?>
                            <span class="badge verificado">Activo</span>
                        <?php elseif ($u['estado_cuenta'] === 'suspendido'): ?>
                            <span class="badge" style="background:#fdecea;color:#c62828">Suspendido</span>
                        <?php else: ?>
                            <span class="badge pendiente">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['rol'] !== 'administrador'): ?>
                            <?php if ($u['estado_cuenta'] === 'activo'): ?>
                                <a href="usuarios.php?suspender=<?= $u['id_usuario'] ?>" class="btn btn-danger" onclick="return confirm('¿Suspender este usuario?')">Suspender</a>
                            <?php else: ?>
                                <a href="usuarios.php?activar=<?= $u['id_usuario'] ?>" class="btn btn-success">Activar</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#999;font-size:12px">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>