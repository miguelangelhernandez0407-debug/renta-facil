<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    mysqli_query($conexion, "UPDATE verificacion_propiedad SET estado='aprobado', fecha_respuesta=NOW() WHERE id_propiedad=$id");
    mysqli_query($conexion, "INSERT INTO historial_estado_propiedad (estado_anterior, estado_nuevo, observacion, id_propiedad, id_usuario) VALUES ('pendiente','aprobado','Aprobado por administrador',$id,{$_SESSION['usuario']})");
}

if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    mysqli_query($conexion, "UPDATE verificacion_propiedad SET estado='rechazado', fecha_respuesta=NOW() WHERE id_propiedad=$id");
    mysqli_query($conexion, "INSERT INTO historial_estado_propiedad (estado_anterior, estado_nuevo, observacion, id_propiedad, id_usuario) VALUES ('pendiente','rechazado','Rechazado por administrador',$id,{$_SESSION['usuario']})");
}

$sql = "SELECT p.*, pub.precio_mensual, vp.estado as estado_verificacion
        FROM propiedad p
        INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
        INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
        ORDER BY vp.fecha_solicitud DESC";

$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <nav class="navbar">
        <h1>Renta Fácil — Admin</h1>
        <div>
            <a href="../propiedades/listar.php">Ver sitio</a>
            <a href="usuarios.php">Usuarios</a>
            <a href="../auth/cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="contenedor">
        <h2 style="margin-bottom:20px">Gestión de propiedades</h2>

        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Ubicación</th>
                    <th>Precio/mes</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) === 0): ?>
                    <tr><td colspan="6" style="text-align:center">No hay propiedades registradas</td></tr>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?= $p['id_propiedad'] ?></td>
                            <td><?= ucfirst($p['tipo_propiedad']) ?></td>
                            <td><?= $p['barrio'] ?>, <?= $p['ciudad'] ?></td>
                            <td>$<?= number_format($p['precio_mensual'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($p['estado_verificacion'] === 'aprobado'): ?>
                                    <span class="badge verificado">Aprobado</span>
                                <?php elseif ($p['estado_verificacion'] === 'rechazado'): ?>
                                    <span class="badge" style="background:#fdecea;color:#c62828">Rechazado</span>
                                <?php else: ?>
                                    <span class="badge pendiente">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['estado_verificacion'] === 'pendiente'): ?>
                                    <a href="panel.php?aprobar=<?= $p['id_propiedad'] ?>" class="btn btn-success" onclick="return confirm('¿Aprobar esta propiedad?')">Aprobar</a>
                                    <a href="panel.php?rechazar=<?= $p['id_propiedad'] ?>" class="btn btn-danger" onclick="return confirm('¿Rechazar esta propiedad?')">Rechazar</a>
                                <?php else: ?>
                                    <a href="historial.php?id=<?= $p['id_propiedad'] ?>" class="btn btn-primary">Ver historial</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>