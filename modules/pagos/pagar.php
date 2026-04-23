<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['usuario'];
    $monto = floatval($_POST['monto']);
    $id_contrato = intval($_POST['id_contrato']);
    $referencia = 'RF-' . strtoupper(uniqid());

    $sql = "INSERT INTO pago_simulado (monto, estado, referencia, id_contrato, id_usuario)
            VALUES ($monto, 'completado', '$referencia', $id_contrato, $id_usuario)";

    if (mysqli_query($conexion, $sql)) {
        $exito = "Pago simulado exitoso. Referencia: $referencia";
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <nav class="navbar">
        <h1>Renta Fácil</h1>
        <div>
            <a href="../propiedades/listar.php">Inicio</a>
            <a href="../busqueda/buscar.php">Buscar</a>
            <a href="../auth/cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="contenedor">
        <h2 style="margin-bottom:20px">Pagos simulados</h2>

        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:12px;margin-bottom:24px">
            <p style="font-size:13px;color:#f57f17">
                ⚠️ Este módulo simula pagos con fines académicos. No se procesan datos financieros reales.
            </p>
        </div>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <div class="contenedor-form" style="max-width:560px;margin:0 0 30px 0">
            <h3 style="margin-bottom:16px;color:#1a73e8">Realizar pago simulado</h3>
            <form method="POST">
                <label style="font-size:13px;color:#666">Selecciona el contrato</label>
                <select name="id_contrato" required>
                    <option value="">Selecciona un contrato activo</option>
                    <?php while ($c = mysqli_fetch_assoc($contratos)): ?>
                        <option value="<?= $c['id_contrato'] ?>">
                            #<?= $c['id_contrato'] ?> — <?= ucfirst($c['tipo_propiedad']) ?> en <?= $c['barrio'] ?> 
                            ($<?= number_format($c['valor_mensual'], 0, ',', '.') ?>/mes)
                        </option>
                    <?php endwhile; ?>
                </select>
                <label style="font-size:13px;color:#666">Monto a pagar</label>
                <input type="number" name="monto" placeholder="Ej: 800000" step="1000" required>
                <button type="submit">Simular pago</button>
            </form>
        </div>

        <h3 style="margin-bottom:16px">Historial de pagos</h3>
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Propiedad</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($historial) === 0): ?>
                    <tr><td colspan="5" style="text-align:center">No hay pagos registrados</td></tr>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($historial)): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:12px"><?= $p['referencia'] ?></td>
                            <td><?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?></td>
                            <td>$<?= number_format($p['monto'], 0, ',', '.') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></td>
                            <td><span class="badge verificado"><?= ucfirst($p['estado']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>