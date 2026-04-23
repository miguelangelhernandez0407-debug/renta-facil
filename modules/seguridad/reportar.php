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
    $id_publicacion = intval($_POST['id_publicacion']);
    $motivo = mysqli_real_escape_string($conexion, $_POST['motivo']);
    $id_usuario = $_SESSION['usuario'];

    $verificar = mysqli_query($conexion, "SELECT id_reporte FROM reporte_publicacion WHERE id_publicacion=$id_publicacion AND id_usuario=$id_usuario");
    
    if (mysqli_num_rows($verificar) > 0) {
        $error = 'Ya reportaste esta publicación anteriormente.';
    } else {
        $sql = "INSERT INTO reporte_publicacion (motivo, id_publicacion, id_usuario) VALUES ('$motivo', $id_publicacion, $id_usuario)";
        if (mysqli_query($conexion, $sql)) {
            $exito = 'Publicación reportada correctamente. El administrador la revisará pronto.';
        } else {
            $error = 'Error al reportar: ' . mysqli_error($conexion);
        }
    }
}

$publicaciones = mysqli_query($conexion, "SELECT pub.id_publicacion, p.tipo_propiedad, p.barrio, p.ciudad FROM publicacion pub INNER JOIN propiedad p ON pub.id_propiedad = p.id_propiedad");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar publicación - Renta Fácil</title>
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

    <div class="contenedor-form" style="max-width:560px">
        <h2>Reportar publicación sospechosa</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST">
            <label style="font-size:13px;color:#666">Selecciona la publicación</label>
            <select name="id_publicacion" required>
                <option value="">Selecciona una publicación</option>
                <?php while ($pub = mysqli_fetch_assoc($publicaciones)): ?>
                    <option value="<?= $pub['id_publicacion'] ?>">
                        #<?= $pub['id_publicacion'] ?> — <?= ucfirst($pub['tipo_propiedad']) ?> en <?= $pub['barrio'] ?>, <?= $pub['ciudad'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label style="font-size:13px;color:#666">Motivo del reporte</label>
            <textarea name="motivo" placeholder="Describe por qué consideras que esta publicación es sospechosa o incumple las normas..." rows="5" required></textarea>
            <button type="submit">Enviar reporte</button>
        </form>

        <div style="margin-top:24px;padding:16px;background:#e8f5e9;border-radius:8px;border:1px solid #c3e6cb">
            <p style="font-size:13px;color:#2e7d32;font-weight:bold">Contacto oficial del administrador</p>
            <p style="font-size:13px;color:#2e7d32">admin@rentafacil.com | Tel: 300-999-9999</p>
            <p style="font-size:12px;color:#388e3c;margin-top:4px">Nunca pagues a intermediarios no verificados.</p>
        </div>
    </div>
</body>
</html>