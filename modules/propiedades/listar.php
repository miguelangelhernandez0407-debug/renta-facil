<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$sql = "SELECT p.*, pub.precio_mensual, pub.descripcion 
        FROM propiedad p 
        INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
        WHERE p.id_propiedad IN (
            SELECT id_propiedad FROM verificacion_propiedad WHERE estado='aprobado'
        )
        ORDER BY pub.fecha_publicacion DESC";

$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propiedades - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor">
        <h2 style="margin-bottom:20px">Propiedades disponibles</h2>

        <?php if (mysqli_num_rows($resultado) === 0): ?>
            <p>No hay propiedades disponibles por el momento.</p>
        <?php else: ?>
            <div class="grid-propiedades">
                <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                    <div class="tarjeta">
                        <div class="tarjeta-info">
                            <h3><?= $p['tipo_propiedad'] ?> en <?= $p['barrio'] ?></h3>
                            <p>📍 <?= $p['ciudad'] ?>, <?= $p['barrio'] ?></p>
                            <p>🛏 <?= $p['habitaciones'] ?> hab. · 🚿 <?= $p['baños'] ?> baños · <?= $p['area_m2'] ?> m²</p>
                            <p><?= $p['descripcion'] ?></p>
                            <p class="precio">$<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes</p>
                            <span class="badge verificado">Verificado</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>