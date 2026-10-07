<?php
$host = "localhost";
$puerto = 3307;
$usuario = "root";
$clave = "";
$base_datos = "activo_fijo";

// Crear conexión con puerto personalizado
$conn = new mysqli($host, $usuario, $clave, $base_datos, $puerto);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
