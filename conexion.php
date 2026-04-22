<?php
$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "renta_facil";

$conexion = mysqli_connect($host, $usuario, $password, $base_datos);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
?>