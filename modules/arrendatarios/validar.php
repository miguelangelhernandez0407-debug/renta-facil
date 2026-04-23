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
    $id_usuario = $_SESSION['usuario'];
    $acepta_declaracion = isset($_POST['acepta_declaracion']) ? 1 : 0;
    $documento_antecedentes = '';

    if (isset($_FILES['documento_antecedentes']) && $_FILES['documento_antecedentes']['error'] === 0) {
        $nombre_archivo = time() . '_' . basename($_FILES['documento_antecedentes']['name']);
        $ruta_destino = '../../uploads/documentos/' . $nombre_archivo;
        if (move_uploaded_file($_FILES['documento_antecedentes']['tmp_name'], $ruta_destino)) {
            $documento_antecedentes = $nombre_archivo;
        } else {
            $error = 'Error al subir el documento.';
        }
    }

    if (!$error) {
        $verificar = mysqli_query($conexion, "SELECT id_antecedente FROM antecedentes_arrendatario WHERE id_usuario=$id_usuario");
        
        if (mysqli_num_rows($verificar) > 0) {
            $sql = "UPDATE antecedentes_arrendatario SET acepta_declaracion=$acepta_declaracion";
            if ($documento_antecedentes) {
                $sql .= ", documento_antecedentes='$documento_antecedentes'";
            }
            $sql .= " WHERE id_usuario=$id_usuario";
        } else {
            $sql = "INSERT INTO antecedentes_arrendatario (documento_antecedentes, acepta_declaracion, id_usuario) 
                    VALUES ('$documento_antecedentes', $acepta_declaracion, $id_usuario)";
        }

        if (mysqli_query($conexion, $sql)) {
            $exito = 'Información guardada correctamente. Tu perfil está siendo validado.';
        } else {
            $error = 'Error al guardar: ' . mysqli_error($conexion);
        }
    }
}

$info_actual = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM antecedentes_arrendatario WHERE id_usuario={$_SESSION['usuario']}"));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validación de arrendatario - Renta Fácil</title>
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
        <h2>Validación de arrendatario</h2>

        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="alerta exito"><?= $exito ?></div>
        <?php endif; ?>

        <?php if ($info_actual): ?>
            <div class="alerta exito" style="margin-bottom:16px">
                ✅ Ya tienes información registrada. Puedes actualizarla a continuación.
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label style="font-size:13px;color:#666">Certificado de antecedentes (opcional — PDF, JPG, PNG)</label>
            <input type="file" name="documento_antecedentes" accept=".pdf,.jpg,.jpeg,.png" style="padding:8px">

            <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:16px;margin-bottom:16px">
                <p style="font-size:13px;color:#333;margin-bottom:12px;font-weight:500">Declaración de veracidad</p>
                <p style="font-size:12px;color:#666;margin-bottom:12px">
                    Declaro bajo la gravedad de juramento que toda la información suministrada en esta plataforma 
                    es verídica y completa. Entiendo que proporcionar información falsa puede resultar en la 
                    suspensión de mi cuenta y acciones legales correspondientes.
                </p>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="acepta_declaracion" value="1" 
                        <?= ($info_actual && $info_actual['acepta_declaracion']) ? 'checked' : '' ?> required>
                    Acepto la declaración de veracidad de información
                </label>
            </div>

            <div style="background:#e3f2fd;border-radius:8px;padding:12px;margin-bottom:16px">
                <p style="font-size:12px;color:#1565c0">
                    🔒 Tu información está protegida conforme a la Ley 1581 de 2012 de protección de datos personales de Colombia.
                </p>
            </div>

            <button type="submit">Guardar validación</button>
        </form>
    </div>
</body>
</html>