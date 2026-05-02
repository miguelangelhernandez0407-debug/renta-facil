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
        $where ORDER BY pub.precio_mensual $orden_sql";

$resultado = mysqli_query($conexion, $sql);
$total = mysqli_num_rows($resultado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar propiedades - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .search-hero {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 40px 32px;
            color: white;
            text-align: center;
        }
        .search-hero h2 { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
        .search-hero p { font-size: 15px; opacity: 0.85; margin-bottom: 24px; }
        .search-bar {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            max-width: 860px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }
        .search-field { display: flex; flex-direction: column; gap: 5px; }
        .search-field label { font-size: 11px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .search-field input,
        .search-field select {
            padding: 10px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            color: #333;
            background: #fafafa;
            transition: border-color 0.2s;
        }
        .search-field input:focus,
        .search-field select:focus {
            outline: none;
            border-color: #1a73e8;
            background: white;
        }
        .search-actions { display: flex; gap: 8px; align-items: flex-end; margin-left: auto; }
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            margin-top: 28px;
        }
        .results-count {
            font-size: 15px;
            color: #555;
            font-weight: 500;
        }
        .results-count strong { color: #1a73e8; font-size: 20px; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 16px; }
        .empty-state p { color: #666; font-size: 16px; margin-bottom: 16px; }
        @media(max-width:768px) {
            .search-bar { flex-direction: column; }
            .search-field input, .search-field select { width: 100%; }
            .search-actions { width: 100%; }
            .search-actions .btn { flex: 1; text-align: center; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <!-- HERO CON BUSCADOR -->
    <div class="search-hero">
        <h2>🔎 Buscar propiedades</h2>
        <p>Encuentra tu lugar ideal con nuestros filtros inteligentes</p>

        <form method="GET" class="search-bar">
            <div class="search-field">
                <label>Barrio</label>
                <input type="text" name="barrio" value="<?= $barrio ?>" placeholder="Ej: Chapinero" style="width:150px">
            </div>
            <div class="search-field">
                <label>Tipo</label>
                <select name="tipo" style="width:150px">
                    <option value="">Todos</option>
                    <option value="casa" <?= $tipo==='casa'?'selected':'' ?>>🏠 Casa</option>
                    <option value="apartamento" <?= $tipo==='apartamento'?'selected':'' ?>>🏢 Apartamento</option>
                    <option value="habitacion" <?= $tipo==='habitacion'?'selected':'' ?>>🛏 Habitación</option>
                    <option value="local" <?= $tipo==='local'?'selected':'' ?>>🏪 Local</option>
                </select>
            </div>
            <div class="search-field">
                <label>Precio mín</label>
                <input type="number" name="precio_min" value="<?= $precio_min ?: '' ?>" placeholder="0" style="width:120px">
            </div>
            <div class="search-field">
                <label>Precio máx</label>
                <input type="number" name="precio_max" value="<?= $precio_max == 99999999 ? '' : $precio_max ?>" placeholder="Sin límite" style="width:120px">
            </div>
            <div class="search-field">
                <label>Ordenar</label>
                <select name="orden" style="width:150px">
                    <option value="ASC" <?= $orden==='ASC'?'selected':'' ?>>↑ Menor precio</option>
                    <option value="DESC" <?= $orden==='DESC'?'selected':'' ?>>↓ Mayor precio</option>
                </select>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn btn-primary" style="padding:10px 20px">Buscar</button>
                <a href="buscar.php" class="btn btn-warning" style="padding:10px 20px">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="contenedor">
        <?php if ($total === 0): ?>
            <div class="empty-state" style="margin-top:28px">
                <div class="icon">🏠</div>
                <p>No se encontraron propiedades con esos criterios.</p>
                <a href="buscar.php" class="btn btn-primary">Ver todas las propiedades</a>
            </div>
        <?php else: ?>
            <div class="results-header">
                <div class="results-count"><strong><?= $total ?></strong> propiedad<?= $total !== 1 ? 'es' : '' ?> encontrada<?= $total !== 1 ? 's' : '' ?></div>
            </div>
            <div class="grid-propiedades">
                <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                    <div class="tarjeta">
                        <div style="background:linear-gradient(135deg,#e8f0fe,#f0f4ff);height:110px;display:flex;align-items:center;justify-content:center;font-size:44px">
                            <?php
                                $iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
                                echo $iconos[$p['tipo_propiedad']] ?? '🏠';
                            ?>
                        </div>
                        <div class="tarjeta-info">
                            <h3>
                                <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $p['id_propiedad'] ?>" style="color:#1a73e8;text-decoration:none">
                                    <?= ucfirst($p['tipo_propiedad']) ?> en <?= $p['barrio'] ?>
                                </a>
                            </h3>
                            <p>📍 <?= $p['ciudad'] ?>, <?= $p['barrio'] ?></p>
                            <p>🛏 <?= $p['habitaciones'] ?> hab. · 🚿 <?= $p['baños'] ?> baños · <?= $p['area_m2'] ?> m²</p>
                            <p style="margin-top:6px;font-size:13px;color:#555"><?= $p['descripcion'] ?></p>
                            <p class="precio">$<?= number_format($p['precio_mensual'], 0, ',', '.') ?>/mes</p>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                                <span class="badge verificado">✓ Verificado</span>
                                <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $p['id_propiedad'] ?>" class="btn btn-primary">Ver detalle</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>