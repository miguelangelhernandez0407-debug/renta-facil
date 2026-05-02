<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

// Marcar como leída
if (isset($_GET['leer'])) {
    $id = intval($_GET['leer']);
    mysqli_query($conexion, "UPDATE notificacion SET leida=1 WHERE id_notificacion=$id AND id_usuario=$id_usuario");
    header("Location: notificaciones.php");
    exit();
}

// Marcar todas como leídas
if (isset($_GET['leer_todas'])) {
    mysqli_query($conexion, "UPDATE notificacion SET leida=1 WHERE id_usuario=$id_usuario");
    header("Location: notificaciones.php");
    exit();
}

$notificaciones = mysqli_query($conexion, "SELECT * FROM notificacion WHERE id_usuario=$id_usuario ORDER BY fecha_envio DESC");
$no_leidas = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM notificacion WHERE id_usuario=$id_usuario AND leida=0"))['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor" style="max-width:700px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2>Notificaciones
                <?php if ($no_leidas > 0): ?>
                    <span style="background:#e53935;color:white;font-size:14px;padding:3px 10px;border-radius:20px;margin-left:8px"><?= $no_leidas ?></span>
                <?php endif; ?>
            </h2>
            <?php if ($no_leidas > 0): ?>
                <a href="notificaciones.php?leer_todas=1" class="btn btn-primary">Marcar todas como leídas</a>
            <?php endif; ?>
        </div>

        <?php if (mysqli_num_rows($notificaciones) === 0): ?>
            <div style="text-align:center;padding:48px;background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <p style="font-size:40px;margin-bottom:12px">🔔</p>
                <p style="color:#666">No tienes notificaciones por el momento.</p>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px">
                <?php while ($n = mysqli_fetch_assoc($notificaciones)): ?>
                    <div style="background:<?= $n['leida'] ? 'white' : '#e8f0fe' ?>;border:1px solid <?= $n['leida'] ? '#eee' : '#c5d8fb' ?>;border-radius:12px;padding:18px 20px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px">
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                                <?php if (!$n['leida']): ?>
                                    <span style="width:8px;height:8px;border-radius:50%;background:#1a73e8;flex-shrink:0;display:inline-block"></span>
                                <?php endif; ?>
                                <p style="font-weight:<?= $n['leida'] ? '400' : '600' ?>;color:#1a1a1a"><?= $n['mensaje'] ?></p>
                            </div>
                            <p style="font-size:12px;color:#999"><?= date('d/m/Y H:i', strtotime($n['fecha_envio'])) ?></p>
                        </div>
                        <?php if (!$n['leida']): ?>
                            <a href="notificaciones.php?leer=<?= $n['id_notificacion'] ?>" style="font-size:12px;color:#1a73e8;white-space:nowrap;padding-top:2px">Marcar leída</a>
                        <?php else: ?>
                            <span style="font-size:12px;color:#999">Leída</span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>