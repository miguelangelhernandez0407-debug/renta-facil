<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'arrendador') {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_propiedad = mysqli_real_escape_string($conexion, $_POST['tipo_propiedad']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $barrio = mysqli_real_escape_string($conexion, $_POST['barrio']);
    $habitaciones = intval($_POST['habitaciones']);
    $banos = intval($_POST['banos']);
    $area_m2 = floatval($_POST['area_m2']);
    $estrato = intval($_POST['estrato']);
    $parqueadero = mysqli_real_escape_string($conexion, $_POST['parqueadero']);
    $precio_mensual = floatval($_POST['precio_mensual']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    $sql_propiedad = "INSERT INTO propiedad (tipo_propiedad, direccion, ciudad, barrio, habitaciones, baños, area_m2, estrato, parqueadero)
                      VALUES ('$tipo_propiedad','$direccion','$ciudad','$barrio',$habitaciones,$banos,$area_m2,$estrato,'$parqueadero')";

    if (mysqli_query($conexion, $sql_propiedad)) {
        $id_propiedad = mysqli_insert_id($conexion);

        $sql_pub = "INSERT INTO publicacion (precio_mensual, descripcion, id_propiedad)
                    VALUES ($precio_mensual,'$descripcion',$id_propiedad)";

        if (mysqli_query($conexion, $sql_pub)) {
            $sql_verif = "INSERT INTO verificacion_propiedad (documento_soporte, estado, id_propiedad)
                          VALUES ('pendiente','pendiente',$id_propiedad)";
            mysqli_query($conexion, $sql_verif);
            $exito = 'Propiedad registrada correctamente. Quedará visible tras ser verificada por el administrador.';
        } else {
            $error = 'Error al crear publicación: ' . mysqli_error($conexion);
        }
    } else {
        $error = 'Error al registrar propiedad: ' . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar propiedad - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <nav class="navbar">
        <h1>Renta Fácil</h1>
        <div>
            <a href="listar.php">Ver propiedades</a>
            <a href="../auth/cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="contenedor-form" style="max-width:600px">
        <h2>Publicar propiedad</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST">
            <select name="tipo_propiedad" required>
                <option value="">Tipo de propiedad</option>
                <option value="casa">Casa</option>
                <option value="apartamento">Apartamento</option>
                <option value="habitacion">Habitación</option>
                <option value="local">Local</option>
            </select>
            <input type="text" name="direccion" placeholder="Dirección exacta" required>
            <input type="text" name="ciudad" placeholder="Ciudad" required>
            <input type="text" name="barrio" placeholder="Barrio" required>
            <input type="number" name="habitaciones" placeholder="Número de habitaciones" min="1" required>
            <input type="number" name="banos" placeholder="Número de baños" min="1" required>
            <input type="number" name="area_m2" placeholder="Área en m²" step="0.01" required>
            <input type="number" name="estrato" placeholder="Estrato (1-6)" min="1" max="6" required>
            <select name="parqueadero" required>
                <option value="">¿Tiene parqueadero?</option>
                <option value="si">Sí</option>
                <option value="no">No</option>
            </select>
            <input type="number" name="precio_mensual" placeholder="Precio mensual en pesos" required>
            <textarea name="descripcion" placeholder="Descripción de la propiedad" rows="4" required></textarea>
            <button type="submit">Publicar propiedad</button>
        </form>
    </div>
</body>
</html>