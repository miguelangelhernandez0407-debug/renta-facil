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

    $sql_propiedad = "INSERT INTO propiedad (tipo_propiedad, direcion, ciudad, barrio, habitaciones, baños, area_m2, estrato, parqueadero)
                      VALUES ('$tipo_propiedad','$direcion','$ciudad','$barrio',$habitaciones,$banos,$area_m2,$estrato,'$parqueadero')";

    if (mysqli_query($conexion, $sql_propiedad)) {
        $id_propiedad = mysqli_insert_id($conexion);
        $sql_pub = "INSERT INTO publicacion (precio_mensual, descripcion, id_propiedad) VALUES ($precio_mensual,'$descripcion',$id_propiedad)";
        if (mysqli_query($conexion, $sql_pub)) {
            $sql_verif = "INSERT INTO verificacion_propiedad (documento_soporte, estado, id_propiedad) VALUES ('pendiente','pendiente',$id_propiedad)";
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
    <style>
        body { background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%); min-height: 100vh; }
        .form-section {
            background: #f8f9ff;
            border: 1px solid #e8f0fe;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-section-title {
            font-size: 12px;
            font-weight: 700;
            color: #1a73e8;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }
        .crear-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(26,115,232,0.1);
            padding: 40px;
            max-width: 640px;
            margin: 40px auto;
        }
        .crear-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .crear-header .icon { font-size: 36px; margin-bottom: 8px; }
        .crear-header h2 { font-size: 24px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .crear-header p { font-size: 14px; color: #888; }
        .crear-box input,
        .crear-box select,
        .crear-box textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: white;
            color: #333;
            margin-bottom: 0;
        }
        .crear-box input:focus,
        .crear-box select:focus,
        .crear-box textarea:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
        }
        .tipo-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 0;
        }
        .tipo-option {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        .tipo-option:hover { border-color: #1a73e8; background: #e8f0fe; }
        .tipo-option input { display: none; }
        .tipo-option.selected { border-color: #1a73e8; background: #e8f0fe; }
        .tipo-option .t-icon { font-size: 24px; margin-bottom: 4px; }
        .tipo-option .t-name { font-size: 12px; font-weight: 600; color: #333; }
        .parq-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .parq-option {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px; font-weight: 500;
        }
        .parq-option:hover { border-color: #1a73e8; background: #e8f0fe; }
        .parq-option input { display: none; }
        .parq-option.selected { border-color: #1a73e8; background: #e8f0fe; color: #1a73e8; }
        .crear-box button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #1a73e8, #0d47a1);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            font-family: 'Inter', Arial, sans-serif;
        }
        .crear-box button:hover { opacity: 0.92; transform: translateY(-1px); }
        @media(max-width:600px) {
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
            .tipo-selector { grid-template-columns: 1fr 1fr; }
            .crear-box { padding: 24px 16px; margin: 20px 16px; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="crear-box">
        <div class="crear-header">
            <div class="icon">🏠</div>
            <h2>Publicar propiedad</h2>
            <p>Llena los datos de tu inmueble para publicarlo</p>
        </div>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- TIPO -->
            <div class="form-section">
                <div class="form-section-title">🏷️ Tipo de propiedad</div>
                <div class="tipo-selector">
                    <label class="tipo-option" id="opt-casa">
                        <input type="radio" name="tipo_propiedad" value="casa" required onclick="selectTipo('casa')">
                        <div class="t-icon">🏠</div>
                        <div class="t-name">Casa</div>
                    </label>
                    <label class="tipo-option" id="opt-apartamento">
                        <input type="radio" name="tipo_propiedad" value="apartamento" onclick="selectTipo('apartamento')">
                        <div class="t-icon">🏢</div>
                        <div class="t-name">Apartamento</div>
                    </label>
                    <label class="tipo-option" id="opt-habitacion">
                        <input type="radio" name="tipo_propiedad" value="habitacion" onclick="selectTipo('habitacion')">
                        <div class="t-icon">🛏</div>
                        <div class="t-name">Habitación</div>
                    </label>
                    <label class="tipo-option" id="opt-local">
                        <input type="radio" name="tipo_propiedad" value="local" onclick="selectTipo('local')">
                        <div class="t-icon">🏪</div>
                        <div class="t-name">Local</div>
                    </label>
                </div>
            </div>

            <!-- UBICACIÓN -->
            <div class="form-section">
                <div class="form-section-title">📍 Ubicación</div>
                <div style="margin-bottom:12px">
                    <input type="text" name="direcion" placeholder="Dirección exacta" required>
                </div>
                <div class="form-row">
                    <input type="text" name="ciudad" placeholder="Ciudad" required>
                    <input type="text" name="barrio" placeholder="Barrio" required>
                </div>
            </div>

            <!-- CARACTERÍSTICAS -->
            <div class="form-section">
                <div class="form-section-title">📐 Características</div>
                <div class="form-row-3">
                    <div>
                        <label style="font-size:12px;color:#888;display:block;margin-bottom:4px">Habitaciones</label>
                        <input type="number" name="habitaciones" placeholder="2" min="1" required>
                    </div>
                    <div>
                        <label style="font-size:12px;color:#888;display:block;margin-bottom:4px">Baños</label>
                        <input type="number" name="banos" placeholder="1" min="1" required>
                    </div>
                    <div>
                        <label style="font-size:12px;color:#888;display:block;margin-bottom:4px">Área m²</label>
                        <input type="number" name="area_m2" placeholder="60" step="0.01" required>
                    </div>
                </div>
                <div style="margin-top:12px">
                    <label style="font-size:12px;color:#888;display:block;margin-bottom:4px">Estrato (1-6)</label>
                    <input type="number" name="estrato" placeholder="3" min="1" max="6" required>
                </div>
            </div>

            <!-- PARQUEADERO -->
            <div class="form-section">
                <div class="form-section-title">🚗 Parqueadero</div>
                <div class="parq-selector">
                    <label class="parq-option" id="parq-si">
                        <input type="radio" name="parqueadero" value="si" required onclick="selectParq('si')">
                        ✅ Sí tiene
                    </label>
                    <label class="parq-option" id="parq-no">
                        <input type="radio" name="parqueadero" value="no" onclick="selectParq('no')">
                        ❌ No tiene
                    </label>
                </div>
            </div>

            <!-- PRECIO Y DESCRIPCIÓN -->
            <div class="form-section">
                <div class="form-section-title">💰 Precio y descripción</div>
                <div style="margin-bottom:12px">
                    <label style="font-size:12px;color:#888;display:block;margin-bottom:4px">Precio mensual en pesos</label>
                    <input type="number" name="precio_mensual" placeholder="800000" required>
                </div>
                <div>
                    <label style="font-size:12px;color:#888;display:block;margin-bottom:4px">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Describe tu propiedad..." required></textarea>
                </div>
            </div>

            <button type="submit">Publicar propiedad →</button>
        </form>
    </div>

    <script>
        function selectTipo(t) {
            ['casa','apartamento','habitacion','local'].forEach(x =>
                document.getElementById('opt-'+x).classList.remove('selected'));
            document.getElementById('opt-'+t).classList.add('selected');
        }
        function selectParq(p) {
            ['si','no'].forEach(x =>
                document.getElementById('parq-'+x).classList.remove('selected'));
            document.getElementById('parq-'+p).classList.add('selected');
        }
    </script>
</body>
</html>