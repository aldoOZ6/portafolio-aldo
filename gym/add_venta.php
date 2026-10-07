<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_producto = $_POST['nombre_producto'];
    $cantidad = $_POST['cantidad'];
    $precio = $_POST['precio'];
    $fecha = $_POST['fecha'];

    $stmt = $conn->prepare("INSERT INTO ventas (nombre_producto, cantidad, precio, fecha) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre_producto, $cantidad, $precio, $fecha]);

    header("Location: ventas.php");
}
?>
