<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];

// Agregar o quitar favorito
if (isset($_GET['toggle'])) {
    $id_propiedad = intval($_GET['toggle']);
    $existe = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id_favorito FROM favorito WHERE id_usuario=$id_usuario AND id_propiedad=$id_propiedad"));
    if ($existe) {
        mysqli_query($conexion, "DELETE FROM favorito WHERE id_usuario=$id_usuario AND id_propiedad=$id_propiedad");
    } else {
        mysqli_query($conexion, "INSERT INTO favorito (id_usuario, id_propiedad) VALUES ($id_usuario, $id_propiedad)");
    }
    $redirect = $_GET['redirect'] ?? 'favoritos.php';
    header("Location: $redirect");
    exit();
}

$favoritos = mysqli_query($conexion, "SELECT p.*, pub.precio_mensual, pub.descripcion
    FROM favorito f
    INNER JOIN propiedad p ON f.id_propiedad = p.id_propiedad
    INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
    WHERE f.id_usuario = $id_usuario
    ORDER BY f.fecha_agregado DESC");

$total = mysqli_num_rows($favoritos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis favoritos - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .favoritos-hero {
            background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%);
            padding: 36px 32px;
            color: white;
            margin-bottom: 28px;
        }
        .favoritos-hero h2 { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .favoritos-hero p { opacity: 0.85; font-size: 14px; }
        .empty-state {
            text-align: center; padding: 60px 20px;
            background: white; border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }
        .empty-state .icon { font-size: 52px; margin-bottom: 14px; }
        .empty-state p { color: #666; font-size: 16px; margin-bottom: 20px; }
        .fav-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
            background: #fdecea; color: #e53935;
            border: 1.5px solid #f5c6cb;
        }
        .fav-btn:hover { background: #e53935; color: white; border-color: #e53935; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="favoritos-hero">
        <div class="contenedor" style="margin:0 auto;padding:0">
            <h2>❤️ Mis favoritos</h2>
            <p><?= $total ?> propiedad<?= $total !== 1 ? 'es' : '' ?> guardada<?= $total !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <div class="contenedor">
        <?php if ($total === 0): ?>
            <div class="empty-state">
                <div class="icon">❤️</div>
                <p>No tienes propiedades guardadas en favoritos.</p>
                <a href="../busqueda/buscar.php" class="btn btn-primary">🔎 Buscar propiedades</a>
            </div>
        <?php else: ?>
            <div class="grid-propiedades">
                <?php
                $iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
                while ($p = mysqli_fetch_assoc($favoritos)): ?>
                    <div class="tarjeta">
                        <div style="background:linear-gradient(135deg,#fdecea,#fff5f5);height:110px;display:flex;align-items:center;justify-content:center;font-size:44px;position:relative">
                            <?= $iconos[$p['tipo_propiedad']] ?? '🏠' ?>
                            <span style="position:absolute;top:10px;right:10px;font-size:20px">❤️</span>
                        </div>
                        <div class="tarjeta-info">
                            <h3>
                                <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $p['id_propiedad'] ?>" style="color:#1a73e8;text-decoration:none">
                                    <?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?>
                                </a>
                            </h3>
                            <p>📍 <?= $p['ciudad'] ?>, <?= $p['barrio'] ?></p>
                            <p>🛏 <?= $p['habitaciones'] ?> hab. · 🚿 <?= $p['baños'] ?> baños · <?= $p['area_m2'] ?> m²</p>
                            <p class="precio">$<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes</p>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                                <a href="/renta-facil/modules/favoritos/favoritos.php?toggle=<?= $p['id_propiedad'] ?>" class="fav-btn">❤️ Quitar</a>
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