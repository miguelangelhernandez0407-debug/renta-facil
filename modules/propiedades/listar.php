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
$total = mysqli_num_rows($resultado);
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

    <div class="hero">
        <h2>Encuentra tu próximo hogar</h2>
        <p>Propiedades verificadas en todo el país, listas para arrendar.</p>
        <a href="../busqueda/buscar.php" class="btn" style="background:white;color:#1a73e8;font-weight:700;padding:12px 28px;font-size:15px">🔎 Buscar propiedades</a>
    </div>

    <div class="contenedor">
        <div class="section-header">
            <h2>Propiedades disponibles <span style="font-size:15px;color:#888;font-weight:400">(<?= $total ?>)</span></h2>
            <?php if ($_SESSION['rol'] === 'arrendador'): ?>
                <a href="../propiedades/crear.php" class="btn btn-primary">+ Publicar propiedad</a>
            <?php endif; ?>
        </div>

        <?php if ($total === 0): ?>
            <div style="text-align:center;padding:60px;background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.07)">
                <p style="font-size:40px;margin-bottom:12px">🏠</p>
                <p style="color:#666;font-size:16px">No hay propiedades disponibles por el momento.</p>
            </div>
        <?php else: ?>
            <div class="grid-propiedades">
                <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                    <div class="tarjeta">
                        <div style="background:linear-gradient(135deg,#e8f0fe,#f0f4ff);height:120px;display:flex;align-items:center;justify-content:center;font-size:48px">
                            <?php
                                $iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
                                echo $iconos[$p['tipo_propiedad']] ?? '🏠';
                            ?>
                        </div>
                        <div class="tarjeta-info">
                            <h3>
                                <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $p['id_propiedad'] ?>" style="color:#1a73e8;text-decoration:none">
                                    <?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?>
                                </a>
                            </h3>
                            <p>📍 <?= $p['ciudad'] ?>, <?= $p['barrio'] ?></p>
                            <p>🛏 <?= $p['habitaciones'] ?> hab. · 🚿 <?= $p['baños'] ?> baños · <?= $p['area_m2'] ?> m²</p>
                            <p style="margin-top:6px;color:#555"><?= $p['descripcion'] ?></p>
                            <p class="precio">$<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes</p>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                                <span class="badge verificado">✓ Verificado</span>
                                <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $p['id_propiedad'] ?>" class="btn btn-primary">Ver detalle</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>