<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta Fácil</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php
    if (isset($_SESSION['usuario'])) {
        header("Location: modules/propiedades/listar.php");
        exit();
    } else {
        header("Location: modules/auth/login.php");
        exit();
    }
    ?>
</body>
</html>