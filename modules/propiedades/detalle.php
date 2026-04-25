<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_propiedad = intval($_GET['id'] ?? 0);

if (!$id_propiedad) {
    header("Location: listar.php");
    exit();
}

$propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, pub.precio_mensual, pub.descripcion, pub.id_publicacion,
    u.nombres, u.apellidos, u.correo, u.telefono
    FROM propiedad p
    INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
    INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
    LEFT JOIN usuario u ON u.rol = 'arrendador'
    WHERE p.id_propiedad = $id_propiedad AND vp.estado = 'aprobado'
    LIMIT 1"));

if (!$propiedad) {
    header("Location: listar.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?> - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor">
        <a href="listar.php" class="btn btn-primary" style="margin-bottom:20px;display:inline-block">← Volver</a>

        <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.1);overflow:hidden">
            
            <div style="background:#1a73e8;padding:24px;color:white">
                <h2 style="margin-bottom:6px"><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?></h2>
                <p style="opacity:0.9">📍 <?= $propiedad['direccion'] ?>, <?= $propiedad['barrio'] ?>, <?= $propiedad['ciudad'] ?></p>
                <p style="font-size:24px;font-weight:bold;margin-top:8px">$<?= number_format($propiedad['precio_mensual'], 0, ',', '.') ?>/mes</p>
                <span style="background:rgba(255,255,255,0.2);padding:4px 12px;border-radius:20px;font-size:12px">✓ Propiedad verificada</span>
            </div>

            <div style="padding:24px">
                <h3 style="margin-bottom:16px;color:#1a73e8">Características</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:24px">
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center">
                        <p style="font-size:20px">🛏</p>
                        <p style="font-weight:bold"><?= $propiedad['habitaciones'] ?></p>
                        <p style="font-size:12px;color:#666">Habitaciones</p>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center">
                        <p style="font-size:20px">🚿</p>
                        <p style="font-weight:bold"><?= $propiedad['baños'] ?></p>
                        <p style="font-size:12px;color:#666">Baños</p>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center">
                        <p style="font-size:20px">📐</p>
                        <p style="font-weight:bold"><?= $propiedad['area_m2'] ?> m²</p>
                        <p style="font-size:12px;color:#666">Área</p>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center">
                        <p style="font-size:20px">🏙</p>
                        <p style="font-weight:bold">Estrato <?= $propiedad['estrato'] ?></p>
                        <p style="font-size:12px;color:#666">Estrato</p>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center">
                        <p style="font-size:20px"><?= $propiedad['parqueadero'] === 'si' ? '🚗' : '❌' ?></p>
                        <p style="font-weight:bold"><?= $propiedad['parqueadero'] === 'si' ? 'Sí' : 'No' ?></p>
                        <p style="font-size:12px;color:#666">Parqueadero</p>
                    </div>
                </div>

                <h3 style="margin-bottom:12px;color:#1a73e8">Descripción</h3>
                <p style="color:#555;line-height:1.6;margin-bottom:24px"><?= $propiedad['descripcion'] ?></p>

                <div style="background:#e8f5e9;border-radius:8px;padding:16px">
                    <h3 style="margin-bottom:12px;color:#2e7d32">Contactar arrendador</h3>
                    <p style="font-size:14px;color:#333"><strong>Nombre:</strong> <?= $propiedad['nombres'] ?> <?= $propiedad['apellidos'] ?></p>
                    <p style="font-size:14px;color:#333"><strong>Correo:</strong> <?= $propiedad['correo'] ?></p>
                    <p style="font-size:14px;color:#333"><strong>Teléfono:</strong> <?= $propiedad['telefono'] ?></p>
                    <div style="margin-top:12px">
                        <a href="mailto:<?= $propiedad['correo'] ?>" class="btn btn-success">Enviar correo</a>
                    </div>
                </div>

                <?php if ($_SESSION['rol'] === 'arrendatario'): ?>
                <div style="margin-top:16px;background:#e8f0fe;border-radius:8px;padding:16px">
                    <p style="font-size:13px;color:#1a73e8">¿Te interesa esta propiedad? Contacta al arrendador directamente o reporta si algo parece sospechoso.</p>
                    <a href="../seguridad/reportar.php" class="btn btn-warning" style="margin-top:8px">Reportar publicación</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>