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
    $id_propiedad = intval($_POST['id_propiedad']);
    $documento = '';

    if (isset($_FILES['documento']) && $_FILES['documento']['error'] === 0) {
        $nombre_archivo = time() . '_' . basename($_FILES['documento']['name']);
        $ruta_destino = '../../uploads/documentos/' . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_destino)) {
            $documento = $nombre_archivo;
        } else {
            $error = 'Error al subir el documento.';
        }
    }

    if (!$error) {
        $sql = "UPDATE verificacion_propiedad SET documento_soporte='$documento' WHERE id_propiedad=$id_propiedad";
        if (mysqli_query($conexion, $sql)) {
            $exito = 'Documento cargado correctamente. El administrador lo revisará pronto.';
        } else {
            $error = 'Error al guardar: ' . mysqli_error($conexion);
        }
    }
}

$propiedades = mysqli_query($conexion, "SELECT p.id_propiedad, p.tipo_propiedad, p.barrio, vp.estado, vp.documento_soporte 
    FROM propiedad p 
    INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de inmueble - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <nav class="navbar">
        <h1>Renta Fácil</h1>
        <div>
            <a href="../propiedades/listar.php">Inicio</a>
            <a href="../auth/cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="contenedor">
        <h2 style="margin-bottom:20px">Verificación de inmuebles</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <div class="contenedor-form" style="max-width:560px;margin:0 0 30px 0">
            <h3 style="margin-bottom:16px;color:#1a73e8">Cargar documento de titularidad</h3>
            <form method="POST" enctype="multipart/form-data">
                <label style="font-size:13px;color:#666">Selecciona la propiedad</label>
                <select name="id_propiedad" required>
                    <option value="">Selecciona una propiedad</option>
                    <?php 
                    $props = mysqli_query($conexion, "SELECT id_propiedad, tipo_propiedad, barrio FROM propiedad");
                    while ($prop = mysqli_fetch_assoc($props)): ?>
                        <option value="<?= $prop['id_propiedad'] ?>">
                            #<?= $prop['id_propiedad'] ?> — <?= ucfirst($prop['tipo_propiedad']) ?> en <?= $prop['barrio'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label style="font-size:13px;color:#666">Documento de titularidad (PDF, JPG, PNG)</label>
                <input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png" required style="padding:8px">
                <button type="submit">Cargar documento</button>
            </form>
        </div>

        <h3 style="margin-bottom:16px">Estado de verificaciones</h3>
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Propiedad</th>
                    <th>Documento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($propiedades, 0);
                $propiedades = mysqli_query($conexion, "SELECT p.id_propiedad, p.tipo_propiedad, p.barrio, vp.estado, vp.documento_soporte 
                    FROM propiedad p 
                    INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad");
                while ($p = mysqli_fetch_assoc($propiedades)): ?>
                    <tr>
                        <td><?= $p['id_propiedad'] ?></td>
                        <td><?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?></td>
                        <td><?= $p['documento_soporte'] ? '✅ Cargado' : '❌ Sin documento' ?></td>
                        <td>
                            <?php if ($p['estado'] === 'aprobado'): ?>
                                <span class="badge verificado">Aprobado</span>
                            <?php elseif ($p['estado'] === 'rechazado'): ?>
                                <span class="badge" style="background:#fdecea;color:#c62828">Rechazado</span>
                            <?php else: ?>
                                <span class="badge pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>