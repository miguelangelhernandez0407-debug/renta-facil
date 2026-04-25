<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_propiedad = intval($_POST['id_propiedad']);
    $id_arrendatario = intval($_POST['id_arrendatario']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_finalizacion = $_POST['fecha_finalizacion'];
    $valor_mensual = floatval($_POST['valor_mensual']);
    $deposito = floatval($_POST['deposito']);

    $sql = "INSERT INTO contrato (fecha_inicio, fecha_finalizacion, valor_mensual, deposito, estado_contrato, id_propiedad, id_arrendatario)
            VALUES ('$fecha_inicio', '$fecha_finalizacion', $valor_mensual, $deposito, 'activo', $id_propiedad, $id_arrendatario)";

    if (mysqli_query($conexion, $sql)) {
        $exito = 'Contrato creado correctamente.';
    } else {
        $error = 'Error al crear contrato: ' . mysqli_error($conexion);
    }
}

$propiedades = mysqli_query($conexion, "SELECT p.id_propiedad, p.tipo_propiedad, p.barrio, pub.precio_mensual
    FROM propiedad p
    INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
    INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
    WHERE vp.estado = 'aprobado'");

$arrendatarios = mysqli_query($conexion, "SELECT id_usuario, nombres, apellidos, correo 
    FROM usuario WHERE rol = 'arrendatario' AND estado_cuenta = 'activo'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear contrato - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor-form" style="max-width:580px">
        <h2>Crear contrato de arrendamiento</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST">
            <label style="font-size:13px;color:#666">Propiedad</label>
            <select name="id_propiedad" required>
                <option value="">Selecciona una propiedad aprobada</option>
                <?php while ($p = mysqli_fetch_assoc($propiedades)): ?>
                    <option value="<?= $p['id_propiedad'] ?>">
                        #<?= $p['id_propiedad'] ?> — <?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?>
                        ($<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes)
                    </option>
                <?php endwhile; ?>
            </select>

            <label style="font-size:13px;color:#666">Arrendatario</label>
            <select name="id_arrendatario" required>
                <option value="">Selecciona un arrendatario</option>
                <?php while ($a = mysqli_fetch_assoc($arrendatarios)): ?>
                    <option value="<?= $a['id_usuario'] ?>">
                        <?= $a['nombres'] ?> <?= $a['apellidos'] ?> — <?= $a['correo'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label style="font-size:13px;color:#666">Fecha de inicio</label>
            <input type="date" name="fecha_inicio" required>

            <label style="font-size:13px;color:#666">Fecha de finalizacion</label>
            <input type="date" name="fecha_finalizacion" required>

            <label style="font-size:13px;color:#666">Valor mensual</label>
            <input type="number" name="valor_mensual" placeholder="Ej: 800000" required>

            <label style="font-size:13px;color:#666">Deposito</label>
            <input type="number" name="deposito" placeholder="Ej: 1600000">

            <button type="submit">Crear contrato</button>
        </form>
    </div>
</body>
</html>