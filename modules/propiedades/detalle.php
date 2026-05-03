<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_propiedad = intval($_GET['id'] ?? 0);
$id_usuario = $_SESSION['usuario'];

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

$es_favorito = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id_favorito FROM favorito WHERE id_usuario=$id_usuario AND id_propiedad=$id_propiedad"));

$iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
$icono = $iconos[$propiedad['tipo_propiedad']] ?? '🏠';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?> - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .detalle-hero {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 48px 32px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .detalle-hero .tipo-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .detalle-hero h2 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .detalle-hero .ubicacion { font-size: 15px; opacity: 0.85; margin-bottom: 16px; }
        .detalle-hero .precio { font-size: 36px; font-weight: 800; margin-bottom: 12px; }
        .detalle-hero .verificado {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 5px 16px; border-radius: 20px; font-size: 13px;
        }
        .fav-hero-btn {
            position: absolute; top: 20px; right: 24px;
            font-size: 28px; text-decoration: none;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 8px 14px; border-radius: 12px;
            transition: all 0.2s;
        }
        .fav-hero-btn:hover { background: rgba(255,255,255,0.25); }
        .detalle-body {
            max-width: 860px; margin: -24px auto 40px;
            padding: 0 20px; position: relative; z-index: 1;
        }
        .detalle-card {
            background: white; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
            overflow: hidden; margin-bottom: 20px;
        }
        .detalle-card-header {
            padding: 18px 24px; border-bottom: 1px solid #f0f4ff;
            font-size: 14px; font-weight: 700; color: #1a73e8;
            letter-spacing: 0.5px; text-transform: uppercase;
            display: flex; align-items: center; gap: 8px;
        }
        .detalle-card-body { padding: 24px; }
        .caract-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }
        .caract-item {
            background: #f8f9ff; border: 1px solid #e8f0fe;
            border-radius: 12px; padding: 16px 8px; text-align: center;
        }
        .caract-item .c-icon { font-size: 26px; margin-bottom: 6px; }
        .caract-item .c-val { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 2px; }
        .caract-item .c-label { font-size: 11px; color: #888; }
        .contacto-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 16px; margin-bottom: 16px;
        }
        .contacto-item {
            background: #f8f9ff; border-radius: 10px; padding: 14px 16px;
        }
        .contacto-item .c-key { font-size: 11px; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .contacto-item .c-val { font-size: 14px; color: #1a1a2e; font-weight: 500; }
        @media(max-width:600px) {
            .caract-grid { grid-template-columns: repeat(3, 1fr); }
            .contacto-grid { grid-template-columns: 1fr; }
            .detalle-hero h2 { font-size: 22px; }
            .detalle-hero .precio { font-size: 28px; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="detalle-hero">
        <a href="/renta-facil/modules/favoritos/favoritos.php?toggle=<?= $id_propiedad ?>&redirect=/renta-facil/modules/propiedades/detalle.php?id=<?= $id_propiedad ?>"
           class="fav-hero-btn" title="<?= $es_favorito ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
            <?= $es_favorito ? '❤️' : '🤍' ?>
        </a>
        <div class="tipo-badge"><?= $icono ?> <?= ucfirst($propiedad['tipo_propiedad']) ?></div>
        <h2><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= ucfirst($propiedad['barrio']) ?></h2>
        <p class="ubicacion">📍 <?= $propiedad['direcion'] ?>, <?= $propiedad['barrio'] ?>, <?= $propiedad['ciudad'] ?></p>
        <p class="precio">$<?= number_format($propiedad['precio_mensual'], 0, ',', '.') ?><span style="font-size:18px;font-weight:400">/mes</span></p>
        <span class="verificado">✓ Propiedad verificada</span>
    </div>

    <div class="detalle-body">
        <a href="listar.php" class="btn btn-primary" style="margin-bottom:20px;display:inline-block">← Volver</a>

        <div class="detalle-card">
            <div class="detalle-card-header">📐 Características</div>
            <div class="detalle-card-body">
                <div class="caract-grid">
                    <div class="caract-item">
                        <div class="c-icon">🛏</div>
                        <div class="c-val"><?= $propiedad['habitaciones'] ?></div>
                        <div class="c-label">Habitaciones</div>
                    </div>
                    <div class="caract-item">
                        <div class="c-icon">🚿</div>
                        <div class="c-val"><?= $propiedad['baños'] ?></div>
                        <div class="c-label">Baños</div>
                    </div>
                    <div class="caract-item">
                        <div class="c-icon">📐</div>
                        <div class="c-val"><?= $propiedad['area_m2'] ?></div>
                        <div class="c-label">m²</div>
                    </div>
                    <div class="caract-item">
                        <div class="c-icon">🏙</div>
                        <div class="c-val"><?= $propiedad['estrato'] ?></div>
                        <div class="c-label">Estrato</div>
                    </div>
                    <div class="caract-item">
                        <div class="c-icon"><?= $propiedad['parqueadero'] === 'si' ? '🚗' : '❌' ?></div>
                        <div class="c-val"><?= $propiedad['parqueadero'] === 'si' ? 'Sí' : 'No' ?></div>
                        <div class="c-label">Parqueadero</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detalle-card">
            <div class="detalle-card-header">📝 Descripción</div>
            <div class="detalle-card-body">
                <p style="color:#555;line-height:1.8;font-size:15px"><?= $propiedad['descripcion'] ?></p>
            </div>
        </div>

        <div class="detalle-card">
            <div class="detalle-card-header">📞 Contactar arrendador</div>
            <div class="detalle-card-body">
                <div class="contacto-grid">
                    <div class="contacto-item">
                        <div class="c-key">Nombre</div>
                        <div class="c-val"><?= $propiedad['nombres'] ?> <?= $propiedad['apellidos'] ?></div>
                    </div>
                    <div class="contacto-item">
                        <div class="c-key">Teléfono</div>
                        <div class="c-val"><?= $propiedad['telefono'] ?></div>
                    </div>
                    <div class="contacto-item" style="grid-column:span 2">
                        <div class="c-key">Correo</div>
                        <div class="c-val"><?= $propiedad['correo'] ?></div>
                    </div>
                </div>
                <a href="mailto:<?= $propiedad['correo'] ?>" class="btn btn-success" style="margin-right:8px">✉️ Enviar correo</a>
                <a href="tel:<?= $propiedad['telefono'] ?>" class="btn btn-primary">📞 Llamar</a>
            </div>
        </div>

        <?php if ($_SESSION['rol'] === 'arrendatario'): ?>
        <div class="detalle-card">
            <div class="detalle-card-header">🚨 ¿Algo sospechoso?</div>
            <div class="detalle-card-body">
                <p style="color:#666;font-size:14px;margin-bottom:12px">Si consideras que esta publicación es fraudulenta o incumple las normas, repórtala al administrador.</p>
                <a href="../seguridad/reportar.php" class="btn btn-warning">Reportar publicación</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>