<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'arrendador') {
    header("Location: ../auth/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];

$propiedades = mysqli_query($conexion, "SELECT p.*, pub.precio_mensual, pub.descripcion, vp.estado as estado_verificacion
    FROM propiedad p
    INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
    INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
    ORDER BY p.fecha_registro DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis propiedades - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2>Mis propiedades</h2>
            <a href="crear.php" class="btn btn-primary">+ Publicar nueva propiedad</a>
        </div>

        <?php if (mysqli_num_rows($propiedades) === 0): ?>
            <div style="text-align:center;padding:40px;background:white;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                <p style="color:#666;margin-bottom:16px">No tienes propiedades publicadas aún.</p>
                <a href="crear.php" class="btn btn-primary">Publicar mi primera propiedad</a>
            </div>
        <?php else: ?>
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Ubicacion</th>
                        <th>Precio/mes</th>
                        <th>Habitaciones</th>
                        <th>Area m2</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($propiedades)): ?>
                    <tr>
                        <td><?= $p['id_propiedad'] ?></td>
                        <td><?= ucfirst($p['tipo_propiedad']) ?></td>
                        <td><?= $p['barrio'] ?>, <?= $p['ciudad'] ?></td>
                        <td>$<?= number_format($p['precio_mensual'], 0, ',', '.') ?></td>
                        <td><?= $p['habitaciones'] ?></td>
                        <td><?= $p['area_m2'] ?> m2</td>
                        <td>
                            <?php if ($p['estado_verificacion'] === 'aprobado'): ?>
                                <span class="badge verificado">Aprobada</span>
                            <?php elseif ($p['estado_verificacion'] === 'rechazado'): ?>
                                <span class="badge" style="background:#fdecea;color:#c62828">Rechazada</span>
                            <?php else: ?>
                                <span class="badge pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>