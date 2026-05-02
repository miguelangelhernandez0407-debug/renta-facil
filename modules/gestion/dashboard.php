<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../auth/login.php");
    exit();
}

// Estadísticas
$total_usuarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuario"))['total'];
$total_arrendadores = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuario WHERE rol='arrendador'"))['total'];
$total_arrendatarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuario WHERE rol='arrendatario'"))['total'];
$total_propiedades = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM propiedad"))['total'];
$propiedades_aprobadas = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM verificacion_propiedad WHERE estado='aprobado'"))['total'];
$propiedades_pendientes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM verificacion_propiedad WHERE estado='pendiente'"))['total'];
$propiedades_rechazadas = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM verificacion_propiedad WHERE estado='rechazado'"))['total'];
$total_contratos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM contrato"))['total'];
$total_pagos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM pago_simulado"))['total'];
$total_ingresos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(monto) as total FROM pago_simulado WHERE estado='completado'"))['total'] ?? 0;
$total_reportes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM reporte_publicacion"))['total'];

// Últimos usuarios registrados
$ultimos_usuarios = mysqli_query($conexion, "SELECT nombres, apellidos, rol, fecha_registro FROM usuario ORDER BY fecha_registro DESC LIMIT 5");

// Últimas propiedades
$ultimas_propiedades = mysqli_query($conexion, "SELECT p.tipo_propiedad, p.barrio, p.ciudad, vp.estado FROM propiedad p INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad ORDER BY p.fecha_registro DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .stat-card .icon {
            font-size: 28px;
        }
        .stat-card .numero {
            font-size: 32px;
            font-weight: 700;
            color: #1a73e8;
            line-height: 1;
        }
        .stat-card .label {
            font-size: 13px;
            color: #666;
        }
        .stat-card.verde .numero { color: #2e7d32; }
        .stat-card.naranja .numero { color: #e65100; }
        .stat-card.rojo .numero { color: #c62828; }
        .dos-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .tabla-reciente {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .tabla-reciente h3 {
            padding: 16px 20px;
            font-size: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .tabla-reciente table {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-reciente td {
            padding: 12px 20px;
            font-size: 13px;
            border-bottom: 1px solid #f5f5f5;
            color: #444;
        }
        .tabla-reciente tr:last-child td { border-bottom: none; }
        @media(max-width:768px) { .dos-col { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor">
        <h2 style="margin-bottom:24px">Dashboard</h2>

        <!-- ESTADÍSTICAS PRINCIPALES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="numero"><?= $total_usuarios ?></div>
                <div class="label">Usuarios totales</div>
            </div>
            <div class="stat-card">
                <div class="icon">🏠</div>
                <div class="numero"><?= $total_arrendadores ?></div>
                <div class="label">Arrendadores</div>
            </div>
            <div class="stat-card">
                <div class="icon">🔍</div>
                <div class="numero"><?= $total_arrendatarios ?></div>
                <div class="label">Arrendatarios</div>
            </div>
            <div class="stat-card verde">
                <div class="icon">✅</div>
                <div class="numero"><?= $propiedades_aprobadas ?></div>
                <div class="label">Propiedades aprobadas</div>
            </div>
            <div class="stat-card naranja">
                <div class="icon">⏳</div>
                <div class="numero"><?= $propiedades_pendientes ?></div>
                <div class="label">Propiedades pendientes</div>
            </div>
            <div class="stat-card rojo">
                <div class="icon">❌</div>
                <div class="numero"><?= $propiedades_rechazadas ?></div>
                <div class="label">Propiedades rechazadas</div>
            </div>
            <div class="stat-card">
                <div class="icon">📄</div>
                <div class="numero"><?= $total_contratos ?></div>
                <div class="label">Contratos activos</div>
            </div>
            <div class="stat-card verde">
                <div class="icon">💳</div>
                <div class="numero"><?= $total_pagos ?></div>
                <div class="label">Pagos realizados</div>
            </div>
            <div class="stat-card verde">
                <div class="icon">💰</div>
                <div class="numero" style="font-size:20px">$<?= number_format($total_ingresos, 0, ',', '.') ?></div>
                <div class="label">Total ingresos simulados</div>
            </div>
            <div class="stat-card rojo">
                <div class="icon">🚨</div>
                <div class="numero"><?= $total_reportes ?></div>
                <div class="label">Reportes recibidos</div>
            </div>
        </div>

        <!-- TABLAS RECIENTES -->
        <div class="dos-col">
            <div class="tabla-reciente">
                <h3>👥 Últimos usuarios registrados</h3>
                <table>
                    <?php while ($u = mysqli_fetch_assoc($ultimos_usuarios)): ?>
                    <tr>
                        <td><?= $u['nombres'] ?> <?= $u['apellidos'] ?></td>
                        <td>
                            <?php if ($u['rol'] === 'administrador'): ?>
                                <span class="badge" style="background:#e8f0fe;color:#1a73e8">Admin</span>
                            <?php elseif ($u['rol'] === 'arrendador'): ?>
                                <span class="badge verificado">Arrendador</span>
                            <?php else: ?>
                                <span class="badge pendiente">Arrendatario</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#999"><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="tabla-reciente">
                <h3>🏠 Últimas propiedades registradas</h3>
                <table>
                    <?php while ($p = mysqli_fetch_assoc($ultimas_propiedades)): ?>
                    <tr>
                        <td><?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?></td>
                        <td><?= $p['ciudad'] ?></td>
                        <td>
                            <?php if ($p['estado'] === 'aprobado'): ?>
                                <span class="badge verificado">Aprobada</span>
                            <?php elseif ($p['estado'] === 'rechazado'): ?>
                                <span class="badge" style="background:#fdecea;color:#c62828">Rechazada</span>
                            <?php else: ?>
                                <span class="badge pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>