<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../auth/login.php");
    exit();
}

$id_propiedad = intval($_GET['id'] ?? 0);

if (!$id_propiedad) {
    header("Location: panel.php");
    exit();
}

$propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, vp.estado 
    FROM propiedad p 
    INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
    WHERE p.id_propiedad = $id_propiedad"));

$historial = mysqli_query($conexion, "SELECT h.*, u.nombres, u.apellidos 
    FROM historial_estado_propiedad h
    INNER JOIN usuario u ON h.id_usuario = u.id_usuario
    WHERE h.id_propiedad = $id_propiedad
    ORDER BY h.fecha_cambio DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor">
        <a href="panel.php" class="btn btn-primary" style="margin-bottom:20px;display:inline-block">← Volver al panel</a>

        <?php if ($propiedad): ?>
            <h2 style="margin-bottom:8px">Historial de propiedad #<?= $id_propiedad ?></h2>
            <p style="color:#666;margin-bottom:20px">
                <?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?>, <?= $propiedad['ciudad'] ?>
            </p>

            <?php if (mysqli_num_rows($historial) === 0): ?>
                <p>No hay historial de cambios para esta propiedad.</p>
            <?php else: ?>
                <table class="tabla-admin">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estado anterior</th>
                            <th>Estado nuevo</th>
                            <th>Observacion</th>
                            <th>Realizado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($h = mysqli_fetch_assoc($historial)): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($h['fecha_cambio'])) ?></td>
                            <td><?= ucfirst($h['estado_anterior']) ?></td>
                            <td>
                                <?php if ($h['estado_nuevo'] === 'aprobado'): ?>
                                    <span class="badge verificado">Aprobado</span>
                                <?php elseif ($h['estado_nuevo'] === 'rechazado'): ?>
                                    <span class="badge" style="background:#fdecea;color:#c62828">Rechazado</span>
                                <?php else: ?>
                                    <span class="badge pendiente"><?= ucfirst($h['estado_nuevo']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $h['observacion'] ?></td>
                            <td><?= $h['nombres'] ?> <?= $h['apellidos'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <p>Propiedad no encontrada.</p>
        <?php endif; ?>
    </div>
</body>
</html>