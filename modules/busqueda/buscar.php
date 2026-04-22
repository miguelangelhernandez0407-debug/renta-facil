<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$barrio = isset($_GET['barrio']) ? mysqli_real_escape_string($conexion, $_GET['barrio']) : '';
$tipo = isset($_GET['tipo']) ? mysqli_real_escape_string($conexion, $_GET['tipo']) : '';
$precio_min = isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : 0;
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 99999999;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';

$where = "WHERE vp.estado = 'aprobado'";
if ($barrio) $where .= " AND p.barrio LIKE '%$barrio%'";
if ($tipo) $where .= " AND p.tipo_propiedad = '$tipo'";
$where .= " AND pub.precio_mensual BETWEEN $precio_min AND $precio_max";

$orden_sql = $orden === 'DESC' ? 'DESC' : 'ASC';

$sql = "SELECT p.*, pub.precio_mensual, pub.descripcion, pub.id_publicacion
        FROM propiedad p
        INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
        INNER JOIN verificacion_propiedad vp ON p.id_propiedad = vp.id_propiedad
        $where
        ORDER BY pub.precio_mensual $orden_sql";

$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar propiedades - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
    <nav class="navbar">
        <h1>Renta Fácil</h1>
        <div>
            <a href="../propiedades/listar.php">Inicio</a>
            <?php if ($_SESSION['rol'] === 'arrendador'): ?>
                <a href="../propiedades/crear.php">Publicar propiedad</a>
            <?php endif; ?>
            <a href="../auth/cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="contenedor">
        <h2 style="margin-bottom:20px">Buscar propiedades</h2>

        <form method="GET" style="background:white;padding:20px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.1);margin-bottom:24px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="font-size:13px;color:#666;display:block;margin-bottom:4px">Barrio</label>
                <input type="text" name="barrio" value="<?= $barrio ?>" placeholder="Ej: Chapinero" style="width:160px">
            </div>
            <div>
                <label style="font-size:13px;color:#666;display:block;margin-bottom:4px">Tipo</label>
                <select name="tipo" style="width:160px">
                    <option value="">Todos</option>
                    <option value="casa" <?= $tipo==='casa'?'selected':'' ?>>Casa</option>
                    <option value="apartamento" <?= $tipo==='apartamento'?'selected':'' ?>>Apartamento</option>
                    <option value="habitacion" <?= $tipo==='habitacion'?'selected':'' ?>>Habitación</option>
                    <option value="local" <?= $tipo==='local'?'selected':'' ?>>Local</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px;color:#666;display:block;margin-bottom:4px">Precio mín</label>
                <input type="number" name="precio_min" value="<?= $precio_min ?>" placeholder="0" style="width:130px">
            </div>
            <div>
                <label style="font-size:13px;color:#666;display:block;margin-bottom:4px">Precio máx</label>
                <input type="number" name="precio_max" value="<?= $precio_max == 99999999 ? '' : $precio_max ?>" placeholder="Sin límite" style="width:130px">
            </div>
            <div>
                <label style="font-size:13px;color:#666;display:block;margin-bottom:4px">Ordenar precio</label>
                <select name="orden" style="width:160px">
                    <option value="ASC" <?= $orden==='ASC'?'selected':'' ?>>Menor a mayor</option>
                    <option value="DESC" <?= $orden==='DESC'?'selected':'' ?>>Mayor a menor</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height:42px">Buscar</button>
            <a href="buscar.php" class="btn btn-warning" style="height:42px;line-height:26px">Limpiar</a>
        </form>

        <?php if (mysqli_num_rows($resultado) === 0): ?>
            <p>No se encontraron propiedades con esos criterios.</p>
        <?php else: ?>
            <p style="margin-bottom:16px;color:#666;font-size:14px"><?= mysqli_num_rows($resultado) ?> propiedad(es) encontrada(s)</p>
            <div class="grid-propiedades">
                <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                    <div class="tarjeta">
                        <div class="tarjeta-info">
                            <h3><?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?></h3>
                            <p>📍 <?= $p['ciudad'] ?>, <?= $p['barrio'] ?></p>
                            <p>🛏 <?= $p['habitaciones'] ?> hab. · 🚿 <?= $p['baños'] ?> baños · <?= $p['area_m2'] ?> m²</p>
                            <p style="margin-top:8px;font-size:13px;color:#555"><?= $p['descripcion'] ?></p>
                            <p class="precio">$<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes</p>
                            <span class="badge verificado">✓ Verificado</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>