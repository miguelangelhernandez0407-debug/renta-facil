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
    header("Location: panel.php?ok=aprobado");
    exit();
}

if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    mysqli_query($conexion, "UPDATE verificacion_propiedad SET estado='rechazado', fecha_respuesta=NOW() WHERE id_propiedad=$id");
    mysqli_query($conexion, "INSERT INTO historial_estado_propiedad (estado_anterior, estado_nuevo, observacion, id_propiedad, id_usuario) VALUES ('pendiente','rechazado','Rechazado por administrador',$id,{$_SESSION['usuario']})");
    header("Location: panel.php?ok=rechazado");
    exit();
}

$sql = "SELECT p.*, pub.precio_mensual, vp.estado as estado_verificacion
        FROM propiedad p
        INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
        INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
        ORDER BY vp.fecha_solicitud DESC";

$resultado = mysqli_query($conexion, $sql);
$total = mysqli_num_rows($resultado);
$pendientes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM verificacion_propiedad WHERE estado='pendiente'"))['total'];
$aprobadas = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM verificacion_propiedad WHERE estado='aprobado'"))['total'];
$rechazadas = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM verificacion_propiedad WHERE estado='rechazado'"))['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .panel-hero {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 32px;
            color: white;
            margin-bottom: 28px;
        }
        .panel-hero h2 { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .panel-hero p { opacity: 0.8; font-size: 14px; }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 20px;
        }
        .stat-mini {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .stat-mini .s-num { font-size: 28px; font-weight: 800; }
        .stat-mini .s-label { font-size: 12px; opacity: 0.85; }
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid #e0e0e0;
            background: white;
            color: #666;
            text-decoration: none;
            transition: all 0.2s;
        }
        .filter-tab:hover { border-color: #1a73e8; color: #1a73e8; }
        .filter-tab.active { background: #1a73e8; border-color: #1a73e8; color: white; }
        .prop-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 12px;
            border: 1px solid #f0f4ff;
            transition: box-shadow 0.2s;
        }
        .prop-card:hover { box-shadow: 0 4px 20px rgba(26,115,232,0.12); }
        .prop-icon {
            width: 56px; height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e8f0fe, #f0f4ff);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; flex-shrink: 0;
        }
        .prop-info { flex: 1; }
        .prop-info h3 { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
        .prop-info p { font-size: 13px; color: #888; }
        .prop-precio { font-size: 16px; font-weight: 700; color: #1a73e8; margin-right: 16px; }
        .prop-actions { display: flex; gap: 8px; align-items: center; }
        @media(max-width:600px) {
            .prop-card { flex-wrap: wrap; }
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="panel-hero">
        <div class="contenedor" style="margin:0 auto;padding:0">
            <h2>🏠 Gestión de propiedades</h2>
            <p>Aprueba o rechaza las propiedades registradas en la plataforma</p>
            <div class="stats-row">
                <div class="stat-mini">
                    <div>
                        <div class="s-num"><?= $pendientes ?></div>
                        <div class="s-label">⏳ Pendientes</div>
                    </div>
                </div>
                <div class="stat-mini">
                    <div>
                        <div class="s-num"><?= $aprobadas ?></div>
                        <div class="s-label">✅ Aprobadas</div>
                    </div>
                </div>
                <div class="stat-mini">
                    <div>
                        <div class="s-num"><?= $rechazadas ?></div>
                        <div class="s-label">❌ Rechazadas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="contenedor">
        <?php if (isset($_GET['ok'])): ?>
            <div class="alerta exito" style="margin-bottom:20px">
                Propiedad <?= $_GET['ok'] === 'aprobado' ? 'aprobada' : 'rechazada' ?> correctamente.
            </div>
        <?php endif; ?>

        <div class="filter-tabs">
            <a href="panel.php" class="filter-tab <?= !isset($_GET['filtro']) ? 'active' : '' ?>">Todas (<?= $total ?>)</a>
            <a href="panel.php?filtro=pendiente" class="filter-tab <?= ($_GET['filtro'] ?? '') === 'pendiente' ? 'active' : '' ?>">⏳ Pendientes (<?= $pendientes ?>)</a>
            <a href="panel.php?filtro=aprobado" class="filter-tab <?= ($_GET['filtro'] ?? '') === 'aprobado' ? 'active' : '' ?>">✅ Aprobadas (<?= $aprobadas ?>)</a>
            <a href="panel.php?filtro=rechazado" class="filter-tab <?= ($_GET['filtro'] ?? '') === 'rechazado' ? 'active' : '' ?>">❌ Rechazadas (<?= $rechazadas ?>)</a>
        </div>

        <?php
        $iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
        $filtro = $_GET['filtro'] ?? '';
        mysqli_data_seek($resultado, 0);
        $mostrado = false;
        while ($p = mysqli_fetch_assoc($resultado)):
            if ($filtro && $p['estado_verificacion'] !== $filtro) continue;
            $mostrado = true;
        ?>
        <div class="prop-card">
            <div class="prop-icon"><?= $iconos[$p['tipo_propiedad']] ?? '🏠' ?></div>
            <div class="prop-info">
                <h3><?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?></h3>
                <p>📍 <?= $p['ciudad'] ?> · #<?= $p['id_propiedad'] ?></p>
            </div>
            <div class="prop-precio">$<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes</div>
            <div class="prop-actions">
                <?php if ($p['estado_verificacion'] === 'aprobado'): ?>
                    <span class="badge verificado">✅ Aprobado</span>
                    <a href="historial.php?id=<?= $p['id_propiedad'] ?>" class="btn btn-primary">Historial</a>
                <?php elseif ($p['estado_verificacion'] === 'rechazado'): ?>
                    <span class="badge" style="background:#fdecea;color:#c62828">❌ Rechazado</span>
                    <a href="historial.php?id=<?= $p['id_propiedad'] ?>" class="btn btn-primary">Historial</a>
                <?php else: ?>
                    <span class="badge pendiente">⏳ Pendiente</span>
                    <a href="panel.php?aprobar=<?= $p['id_propiedad'] ?>" class="btn btn-success" onclick="return confirm('¿Aprobar esta propiedad?')">Aprobar</a>
                    <a href="panel.php?rechazar=<?= $p['id_propiedad'] ?>" class="btn btn-danger" onclick="return confirm('¿Rechazar?')">Rechazar</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>

        <?php if (!$mostrado): ?>
            <div style="text-align:center;padding:48px;background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.07)">
                <p style="font-size:36px;margin-bottom:12px">🏠</p>
                <p style="color:#666">No hay propiedades en este estado.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>