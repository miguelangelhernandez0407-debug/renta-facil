<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];
$id_propiedad = intval($_GET['id'] ?? 0);

if (!$id_propiedad) {
    header("Location: ../propiedades/listar.php");
    exit();
}

$propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, pub.precio_mensual FROM propiedad p INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad WHERE p.id_propiedad = $id_propiedad"));

if (!$propiedad) {
    header("Location: ../propiedades/listar.php");
    exit();
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calificacion = intval($_POST['calificacion']);
    $comentario = mysqli_real_escape_string($conexion, $_POST['comentario']);

    if ($calificacion < 1 || $calificacion > 5) {
        $error = 'La calificación debe ser entre 1 y 5 estrellas.';
    } else {
        $existe = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id_resena FROM resena WHERE id_usuario=$id_usuario AND id_propiedad=$id_propiedad"));
        if ($existe) {
            $sql = "UPDATE resena SET calificacion=$calificacion, comentario='$comentario' WHERE id_usuario=$id_usuario AND id_propiedad=$id_propiedad";
        } else {
            $sql = "INSERT INTO resena (calificacion, comentario, id_usuario, id_propiedad) VALUES ($calificacion, '$comentario', $id_usuario, $id_propiedad)";
        }
        if (mysqli_query($conexion, $sql)) {
            $exito = 'Reseña guardada correctamente.';
        } else {
            $error = 'Error al guardar: ' . mysqli_error($conexion);
        }
    }
}

$mi_resena = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM resena WHERE id_usuario=$id_usuario AND id_propiedad=$id_propiedad"));
$resenas = mysqli_query($conexion, "SELECT r.*, u.nombres, u.apellidos FROM resena r INNER JOIN usuario u ON r.id_usuario = u.id_usuario WHERE r.id_propiedad=$id_propiedad ORDER BY r.fecha DESC");
$stats = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total, AVG(calificacion) as promedio FROM resena WHERE id_propiedad=$id_propiedad"));
$total_resenas = $stats['total'];
$promedio = round($stats['promedio'], 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reseñas - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body { background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%); min-height: 100vh; }
        .resenas-hero {
            background: linear-gradient(135deg, #f57f17 0%, #e65100 100%);
            padding: 36px 32px; color: white; margin-bottom: 28px;
        }
        .resenas-hero h2 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .resenas-hero p { opacity: 0.85; font-size: 14px; }
        .promedio-box {
            display: flex; align-items: center; gap: 16px; margin-top: 16px;
        }
        .promedio-num {
            font-size: 48px; font-weight: 800; line-height: 1;
        }
        .estrellas { font-size: 22px; }
        .promedio-info { font-size: 13px; opacity: 0.8; margin-top: 4px; }
        .resena-card {
            background: white; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden; margin-bottom: 20px;
        }
        .resena-card-header {
            padding: 18px 24px; border-bottom: 1px solid #f0f4ff;
            font-size: 14px; font-weight: 700; color: #f57f17;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .resena-card-body { padding: 24px; }
        .star-selector { display: flex; gap: 8px; margin-bottom: 16px; }
        .star-selector input { display: none; }
        .star-selector label {
            font-size: 32px; cursor: pointer;
            filter: grayscale(1); transition: filter 0.2s, transform 0.1s;
        }
        .star-selector label:hover,
        .star-selector label:hover ~ label,
        .star-selector input:checked ~ label { filter: none; }
        .star-selector input:checked + label { transform: scale(1.1); }
        .resena-card-body textarea {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; font-family: 'Inter', Arial, sans-serif;
            background: #fafafa; resize: vertical;
            transition: border-color 0.2s; margin-bottom: 16px;
        }
        .resena-card-body textarea:focus {
            outline: none; border-color: #f57f17;
            box-shadow: 0 0 0 3px rgba(245,127,23,0.1);
            background: white;
        }
        .resena-card-body button {
            padding: 12px 28px;
            background: linear-gradient(90deg, #f57f17, #e65100);
            color: white; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', Arial, sans-serif;
            transition: opacity 0.2s;
        }
        .resena-card-body button:hover { opacity: 0.9; }
        .resena-item {
            padding: 18px 0; border-bottom: 1px solid #f0f4ff;
        }
        .resena-item:last-child { border-bottom: none; }
        .resena-top {
            display: flex; justify-content: space-between;
            align-items: flex-start; margin-bottom: 8px;
        }
        .resena-autor { font-weight: 600; font-size: 15px; color: #1a1a2e; }
        .resena-fecha { font-size: 12px; color: #999; }
        .resena-estrellas { font-size: 16px; margin-bottom: 6px; }
        .resena-comentario { font-size: 14px; color: #555; line-height: 1.6; }
        .empty-resenas {
            text-align: center; padding: 32px;
            color: #888; font-size: 15px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="resenas-hero">
        <div class="contenedor" style="margin:0 auto;padding:0">
            <h2>⭐ Reseñas — <?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?></h2>
            <p>$<?= number_format($propiedad['precio_mensual'], 0, ',', '.') ?>/mes · <?= $propiedad['ciudad'] ?></p>
            <?php if ($total_resenas > 0): ?>
            <div class="promedio-box">
                <div class="promedio-num"><?= $promedio ?></div>
                <div>
                    <div class="estrellas">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= round($promedio) ? '⭐' : '☆' ?>
                        <?php endfor; ?>
                    </div>
                    <div class="promedio-info"><?= $total_resenas ?> reseña<?= $total_resenas !== 1 ? 's' : '' ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="contenedor">
        <?php if ($error): ?>
            <div class="alerta error" style="margin-bottom:16px"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito" style="margin-bottom:16px"><?= $exito ?></div>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <div class="resena-card" style="margin-bottom:24px">
            <div class="resena-card-header">
                <?= $mi_resena ? '✏️ Editar mi reseña' : '✍️ Escribir reseña' ?>
            </div>
            <div class="resena-card-body">
                <form method="POST">
                    <p style="font-size:13px;color:#666;margin-bottom:10px">Selecciona tu calificación:</p>
                    <div class="star-selector">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="calificacion" id="star<?= $i ?>" value="<?= $i ?>"
                                <?= ($mi_resena && $mi_resena['calificacion'] == $i) ? 'checked' : '' ?> required>
                            <label for="star<?= $i ?>">⭐</label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="comentario" rows="4" placeholder="Comparte tu experiencia con esta propiedad..."><?= $mi_resena['comentario'] ?? '' ?></textarea>
                    <button type="submit">Publicar reseña →</button>
                    <?php if ($mi_resena): ?>
                        <span style="font-size:13px;color:#888;margin-left:12px">Ya tienes una reseña — se actualizará.</span>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- LISTA DE RESEÑAS -->
        <div class="resena-card">
            <div class="resena-card-header">💬 Todas las reseñas (<?= $total_resenas ?>)</div>
            <div class="resena-card-body">
                <?php if ($total_resenas === 0): ?>
                    <div class="empty-resenas">
                        <p style="font-size:32px;margin-bottom:8px">⭐</p>
                        <p>Sé el primero en dejar una reseña.</p>
                    </div>
                <?php else: ?>
                    <?php while ($r = mysqli_fetch_assoc($resenas)): ?>
                    <div class="resena-item">
                        <div class="resena-top">
                            <div class="resena-autor"><?= $r['nombres'] ?> <?= $r['apellidos'] ?></div>
                            <div class="resena-fecha"><?= date('d/m/Y', strtotime($r['fecha'])) ?></div>
                        </div>
                        <div class="resena-estrellas">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $r['calificacion'] ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                        <p class="resena-comentario"><?= $r['comentario'] ?: '<em style="color:#bbb">Sin comentario.</em>' ?></p>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top:16px">
            <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $id_propiedad ?>" class="btn btn-primary">← Volver al detalle</a>
        </div>
    </div>
</body>
</html>