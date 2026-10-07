<?php
$servername = "localhost";
$username = "root";
$password = ""; // Sin contraseña
$dbname = "gym_temoaya";
$port = 3307;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa";  // Línea comentada para evitar interferencias
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
