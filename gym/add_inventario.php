<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $tipo = $_POST['tipo'];
    $cantidad = $_POST['cantidad']; // ✅ Nueva línea para recibir la cantidad

    $stmt = $conn->prepare("INSERT INTO inventario (nombre, marca, tipo, cantidad) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $marca, $tipo, $cantidad]);

    header("Location: inventario.php");
}
?>