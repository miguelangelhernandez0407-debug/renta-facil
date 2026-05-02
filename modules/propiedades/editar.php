<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'arrendador') {
    header("Location: ../auth/login.php");
    exit();
}

$id_propiedad = intval($_GET['id'] ?? 0);

if (!$id_propiedad) {
    header("Location: mis_propiedades.php");
    exit();
}

$propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, pub.precio_mensual, pub.descripcion, pub.id_publicacion
    FROM propiedad p
    INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
    WHERE p.id_propiedad = $id_propiedad"));

if (!$propiedad) {
    header("Location: mis_propiedades.php");
    exit();
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_propiedad = mysqli_real_escape_string($conexion, $_POST['tipo_propiedad']);
    $direcion = mysqli_real_escape_string($conexion, $_POST['direcion']);
    $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $barrio = mysqli_real_escape_string($conexion, $_POST['barrio']);
    $habitaciones = intval($_POST['habitaciones']);
    $banos = intval($_POST['banos']);
    $area_m2 = floatval($_POST['area_m2']);
    $estrato = intval($_POST['estrato']);
    $parqueadero = mysqli_real_escape_string($conexion, $_POST['parqueadero']);
    $precio_mensual = floatval($_POST['precio_mensual']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    $sql_prop = "UPDATE propiedad SET tipo_propiedad='$tipo_propiedad', direcion='$direcion', ciudad='$ciudad',
        barrio='$barrio', habitaciones=$habitaciones, baños=$banos, area_m2=$area_m2,
        estrato=$estrato, parqueadero='$parqueadero' WHERE id_propiedad=$id_propiedad";

    $sql_pub = "UPDATE publicacion SET precio_mensual=$precio_mensual, descripcion='$descripcion'
        WHERE id_publicacion={$propiedad['id_publicacion']}";

    if (mysqli_query($conexion, $sql_prop) && mysqli_query($conexion, $sql_pub)) {
        $exito = 'Propiedad actualizada correctamente.';
        $propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, pub.precio_mensual, pub.descripcion, pub.id_publicacion
            FROM propiedad p
            INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
            WHERE p.id_propiedad = $id_propiedad"));
    } else {
        $error = 'Error al actualizar: ' . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar propiedad - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="contenedor-form" style="max-width:600px">
        <h2>Editar propiedad</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST">
            <select name="tipo_propiedad" required>
                <option value="">Tipo de propiedad</option>
                <option value="casa" <?= $propiedad['tipo_propiedad']==='casa'?'selected':'' ?>>Casa</option>
                <option value="apartamento" <?= $propiedad['tipo_propiedad']==='apartamento'?'selected':'' ?>>Apartamento</option>
                <option value="habitacion" <?= $propiedad['tipo_propiedad']==='habitacion'?'selected':'' ?>>Habitación</option>
                <option value="local" <?= $propiedad['tipo_propiedad']==='local'?'selected':'' ?>>Local</option>
            </select>
            <input type="text" name="direcion" value="<?= $propiedad['direcion'] ?>" placeholder="Dirección exacta" required>
            <input type="text" name="ciudad" value="<?= $propiedad['ciudad'] ?>" placeholder="Ciudad" required>
            <input type="text" name="barrio" value="<?= $propiedad['barrio'] ?>" placeholder="Barrio" required>
            <input type="number" name="habitaciones" value="<?= $propiedad['habitaciones'] ?>" placeholder="Habitaciones" min="1" required>
            <input type="number" name="banos" value="<?= $propiedad['baños'] ?>" placeholder="Baños" min="1" required>
            <input type="number" name="area_m2" value="<?= $propiedad['area_m2'] ?>" placeholder="Área m²" step="0.01" required>
            <input type="number" name="estrato" value="<?= $propiedad['estrato'] ?>" placeholder="Estrato" min="1" max="6" required>
            <select name="parqueadero" required>
                <option value="">¿Tiene parqueadero?</option>
                <option value="si" <?= $propiedad['parqueadero']==='si'?'selected':'' ?>>Sí</option>
                <option value="no" <?= $propiedad['parqueadero']==='no'?'selected':'' ?>>No</option>
            </select>
            <input type="number" name="precio_mensual" value="<?= $propiedad['precio_mensual'] ?>" placeholder="Precio mensual" required>
            <textarea name="descripcion" rows="4" required><?= $propiedad['descripcion'] ?></textarea>
            <button type="submit">Guardar cambios</button>
            <a href="mis_propiedades.php" class="btn btn-warning" style="display:block;text-align:center;margin-top:8px">Cancelar</a>
        </form>
    </div>
</body>
</html>