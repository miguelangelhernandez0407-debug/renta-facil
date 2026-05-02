<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$exito = '';
$referencia_exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['usuario'];
    $monto = floatval($_POST['monto']);
    $id_contrato = intval($_POST['id_contrato']);
    $referencia = 'RF-' . strtoupper(uniqid());

    $sql = "INSERT INTO pago_simulado (monto, estado, referencia, id_contrato, id_usuario)
            VALUES ($monto, 'completado', '$referencia', $id_contrato, $id_usuario)";

    if (mysqli_query($conexion, $sql)) {
        $exito = "Pago procesado exitosamente.";
        $referencia_exito = $referencia;
    } else {
        $error = 'Error al procesar el pago: ' . mysqli_error($conexion);
    }
}

$contratos = mysqli_query($conexion, "SELECT c.id_contrato, c.valor_mensual, c.estado_contrato, p.tipo_propiedad, p.barrio
    FROM contrato c
    INNER JOIN propiedad p ON c.id_propiedad = p.id_propiedad
    WHERE c.estado_contrato = 'activo'");

$historial = mysqli_query($conexion, "SELECT ps.*, p.tipo_propiedad, p.barrio 
    FROM pago_simulado ps
    INNER JOIN contrato c ON ps.id_contrato = c.id_contrato
    INNER JOIN propiedad p ON c.id_propiedad = p.id_propiedad
    WHERE ps.id_usuario = {$_SESSION['usuario']}
    ORDER BY ps.fecha_pago DESC");

$total_pagado = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(monto) as total FROM pago_simulado WHERE id_usuario={$_SESSION['usuario']} AND estado='completado'"))['total'] ?? 0;
$num_pagos = mysqli_num_rows($historial);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .pagos-hero {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 36px 32px;
            color: white;
            margin-bottom: 28px;
        }
        .pagos-hero h2 { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .pagos-hero p { opacity: 0.8; font-size: 14px; margin-bottom: 20px; }
        .pagos-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            max-width: 400px;
        }
        .pago-stat {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 14px 18px;
        }
        .pago-stat .ps-num { font-size: 24px; font-weight: 800; }
        .pago-stat .ps-label { font-size: 12px; opacity: 0.8; margin-top: 2px; }
        .aviso-academico {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #f57f17;
            font-weight: 500;
        }
        .pago-form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(26,115,232,0.1);
            overflow: hidden;
            margin-bottom: 28px;
        }
        .pago-form-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f0f4ff;
            font-size: 14px; font-weight: 700;
            color: #1a73e8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .pago-form-body { padding: 24px; }
        .pago-form-body label {
            font-size: 12px; font-weight: 600;
            color: #888; text-transform: uppercase;
            letter-spacing: 0.5px; display: block; margin-bottom: 6px;
        }
        .pago-form-body select,
        .pago-form-body input {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px; font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            background: #fafafa; color: #333;
            margin-bottom: 16px;
            transition: border-color 0.2s;
        }
        .pago-form-body select:focus,
        .pago-form-body input:focus {
            outline: none; border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
            background: white;
        }
        .pago-form-body button {
            width: 100%; padding: 14px;
            background: linear-gradient(90deg, #1a73e8, #0d47a1);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', Arial, sans-serif;
            transition: opacity 0.2s, transform 0.1s;
        }
        .pago-form-body button:hover { opacity: 0.92; transform: translateY(-1px); }
        .exito-pago {
            background: #e8f5e9;
            border: 1px solid #c3e6cb;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .exito-pago .check { font-size: 40px; margin-bottom: 8px; }
        .exito-pago h3 { color: #2e7d32; font-size: 18px; margin-bottom: 6px; }
        .exito-pago .ref {
            font-family: monospace; font-size: 16px;
            background: #f1f8e9; padding: 8px 16px;
            border-radius: 8px; color: #2e7d32;
            display: inline-block; margin-top: 8px;
            border: 1px solid #c3e6cb;
        }
        .historial-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(26,115,232,0.1);
            overflow: hidden;
        }
        .historial-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f0f4ff;
            font-size: 14px; font-weight: 700;
            color: #1a73e8; letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .pago-row {
            display: flex; align-items: center; gap: 16px;
            padding: 16px 24px;
            border-bottom: 1px solid #f8f9ff;
            transition: background 0.15s;
        }
        .pago-row:last-child { border-bottom: none; }
        .pago-row:hover { background: #f8f9ff; }
        .pago-row .p-icon {
            width: 44px; height: 44px; border-radius: 10px;
            background: linear-gradient(135deg, #e8f0fe, #f0f4ff);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .pago-row .p-info { flex: 1; }
        .pago-row .p-info h4 { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 3px; }
        .pago-row .p-info p { font-size: 12px; color: #888; }
        .pago-row .p-ref { font-family: monospace; font-size: 11px; color: #999; }
        .pago-row .p-monto { font-size: 16px; font-weight: 700; color: #1a73e8; }
        @media(max-width:600px) {
            .pagos-stats { grid-template-columns: 1fr; }
            .pago-row { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="pagos-hero">
        <div class="contenedor" style="margin:0 auto;padding:0">
            <h2>💳 Pagos simulados</h2>
            <p>Módulo académico — no se procesan datos financieros reales</p>
            <div class="pagos-stats">
                <div class="pago-stat">
                    <div class="ps-num"><?= $num_pagos ?></div>
                    <div class="ps-label">Pagos realizados</div>
                </div>
                <div class="pago-stat">
                    <div class="ps-num" style="font-size:18px">$<?= number_format($total_pagado, 0, ',', '.') ?></div>
                    <div class="ps-label">Total pagado</div>
                </div>
            </div>
        </div>
    </div>

    <div class="contenedor">
        <div class="aviso-academico">
            ⚠️ Este módulo simula pagos con fines académicos. No se procesan datos financieros reales.
        </div>

        <?php if ($error): ?>
            <div class="alerta error" style="margin-bottom:20px"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="exito-pago">
                <div class="check">✅</div>
                <h3><?= $exito ?></h3>
                <p style="color:#555;font-size:14px">Tu referencia de pago es:</p>
                <div class="ref"><?= $referencia_exito ?></div>
            </div>
        <?php endif; ?>

        <!-- FORM PAGO -->
        <div class="pago-form-card">
            <div class="pago-form-header">💰 Realizar pago</div>
            <div class="pago-form-body">
                <form method="POST">
                    <label>Contrato</label>
                    <select name="id_contrato" required>
                        <option value="">Selecciona un contrato activo</option>
                        <?php while ($c = mysqli_fetch_assoc($contratos)): ?>
                            <option value="<?= $c['id_contrato'] ?>">
                                #<?= $c['id_contrato'] ?> — <?= ucfirst($c['tipo_propiedad']) ?> en <?= $c['barrio'] ?>
                                ($<?= number_format($c['valor_mensual'], 0, ',', '.') ?>/mes)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <label>Monto a pagar</label>
                    <input type="number" name="monto" placeholder="Ej: 800000" step="1000" required>
                    <button type="submit">💳 Simular pago →</button>
                </form>
            </div>
        </div>

        <!-- HISTORIAL -->
        <div class="historial-card">
            <div class="historial-header">📋 Historial de pagos</div>
            <?php if (mysqli_num_rows($historial) === 0): ?>
                <div style="text-align:center;padding:40px;color:#888">
                    <p style="font-size:32px;margin-bottom:8px">💳</p>
                    <p>No hay pagos registrados aún.</p>
                </div>
            <?php else: ?>
                <?php while ($p = mysqli_fetch_assoc($historial)): ?>
                <div class="pago-row">
                    <div class="p-icon">🏠</div>
                    <div class="p-info">
                        <h4><?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?></h4>
                        <p><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></p>
                        <div class="p-ref"><?= $p['referencia'] ?></div>
                    </div>
                    <div class="p-monto">$<?= number_format($p['monto'], 0, ',', '.') ?></div>
                    <span class="badge verificado">✓ <?= ucfirst($p['estado']) ?></span>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>