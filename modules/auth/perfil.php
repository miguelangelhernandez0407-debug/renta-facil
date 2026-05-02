<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$exito = '';
$id_usuario = $_SESSION['usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
    $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    $usuario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM usuario WHERE id_usuario=$id_usuario"));

    if (!empty($contrasena_actual)) {
        if (!password_verify($contrasena_actual, $usuario['contraseña'])) {
            $error = 'La contraseña actual es incorrecta.';
        } elseif (empty($nueva_contrasena)) {
            $error = 'Debes ingresar la nueva contraseña.';
        } else {
            $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            mysqli_query($conexion, "UPDATE usuario SET nombres='$nombres', apellidos='$apellidos', telefono='$telefono', contraseña='$hash' WHERE id_usuario=$id_usuario");
            $exito = 'Perfil y contraseña actualizados correctamente.';
        }
    } else {
        mysqli_query($conexion, "UPDATE usuario SET nombres='$nombres', apellidos='$apellidos', telefono='$telefono' WHERE id_usuario=$id_usuario");
        $exito = 'Perfil actualizado correctamente.';
    }
    $_SESSION['nombre'] = $nombres;
}

$usuario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM usuario WHERE id_usuario=$id_usuario"));
$inicial = strtoupper(substr($usuario['nombres'], 0, 1));
$roles = ['administrador' => ['label'=>'Administrador','color'=>'#1a73e8','bg'=>'#e8f0fe'],
          'arrendador'    => ['label'=>'Arrendador',   'color'=>'#2e7d32','bg'=>'#e8f5e9'],
          'arrendatario'  => ['label'=>'Arrendatario', 'color'=>'#e65100','bg'=>'#fff3e0']];
$rol_info = $roles[$usuario['rol']] ?? ['label'=>ucfirst($usuario['rol']),'color'=>'#666','bg'=>'#f5f5f5'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body { background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%); min-height: 100vh; }
        .perfil-wrapper { max-width: 600px; margin: 32px auto; padding: 0 20px; }
        .perfil-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(26,115,232,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .perfil-hero {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            padding: 32px;
            text-align: center;
            color: white;
        }
        .avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: 700;
            margin: 0 auto 14px;
        }
        .perfil-hero h2 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .perfil-hero .correo { font-size: 14px; opacity: 0.8; margin-bottom: 12px; }
        .rol-pill {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 24px;
        }
        .info-item {
            background: #f8f9ff;
            border: 1px solid #e8f0fe;
            border-radius: 10px;
            padding: 12px 16px;
        }
        .info-item .i-key {
            font-size: 11px; color: #888;
            font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 4px;
        }
        .info-item .i-val { font-size: 14px; color: #1a1a2e; font-weight: 500; }
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(26,115,232,0.1);
            overflow: hidden;
        }
        .form-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f0f4ff;
            font-size: 14px; font-weight: 700;
            color: #1a73e8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .form-card-body { padding: 24px; }
        .form-card-body label {
            font-size: 12px; font-weight: 600;
            color: #888; text-transform: uppercase;
            letter-spacing: 0.5px; display: block; margin-bottom: 6px;
        }
        .form-card-body input {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px; font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            background: #fafafa; color: #333;
            margin-bottom: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-card-body input:focus {
            outline: none; border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
            background: white;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .divider { height: 1px; background: #f0f4ff; margin: 8px 0 20px; }
        .form-card-body button {
            width: 100%; padding: 13px;
            background: linear-gradient(90deg, #1a73e8, #0d47a1);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', Arial, sans-serif;
            transition: opacity 0.2s, transform 0.1s;
        }
        .form-card-body button:hover { opacity: 0.92; transform: translateY(-1px); }
        @media(max-width:500px) {
            .info-grid, .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="perfil-wrapper">

        <?php if ($error): ?>
            <div class="alerta error" style="margin-bottom:16px"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito" style="margin-bottom:16px"><?= $exito ?></div>
        <?php endif; ?>

        <!-- CARD PERFIL -->
        <div class="perfil-card">
            <div class="perfil-hero">
                <div class="avatar"><?= $inicial ?></div>
                <h2><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h2>
                <p class="correo">📧 <?= $usuario['correo'] ?></p>
                <span class="rol-pill">
                    <?php
                        $iconos_rol = ['administrador'=>'⚙️','arrendador'=>'🏠','arrendatario'=>'🔍'];
                        echo ($iconos_rol[$usuario['rol']] ?? '👤') . ' ' . $rol_info['label'];
                    ?>
                </span>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="i-key">Documento</div>
                    <div class="i-val"><?= $usuario['tipo_documento'] ?>: <?= $usuario['numero_documento'] ?></div>
                </div>
                <div class="info-item">
                    <div class="i-key">Teléfono</div>
                    <div class="i-val"><?= $usuario['telefono'] ?: 'No registrado' ?></div>
                </div>
                <div class="info-item">
                    <div class="i-key">Estado</div>
                    <div class="i-val" style="color:#2e7d32">✅ <?= ucfirst($usuario['estado_cuenta']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-key">Miembro desde</div>
                    <div class="i-val"><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></div>
                </div>
            </div>
        </div>

        <!-- FORM EDITAR -->
        <div class="form-card">
            <div class="form-card-header">✏️ Editar información</div>
            <div class="form-card-body">
                <form method="POST">
                    <div class="form-row">
                        <div>
                            <label>Nombres</label>
                            <input type="text" name="nombres" value="<?= $usuario['nombres'] ?>" required>
                        </div>
                        <div>
                            <label>Apellidos</label>
                            <input type="text" name="apellidos" value="<?= $usuario['apellidos'] ?>" required>
                        </div>
                    </div>
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="<?= $usuario['telefono'] ?>" placeholder="Tu número de teléfono">

                    <div class="divider"></div>
                    <p style="font-size:12px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:14px">🔒 Cambiar contraseña (opcional)</p>

                    <label>Contraseña actual</label>
                    <input type="password" name="contrasena_actual" placeholder="Deja vacío si no quieres cambiarla">

                    <label>Nueva contraseña</label>
                    <input type="password" name="nueva_contrasena" placeholder="Nueva contraseña">

                    <button type="submit">Guardar cambios →</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>