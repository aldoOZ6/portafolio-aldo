<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $municipio = $_POST['municipio'];

    $stmt = $conn->prepare("INSERT INTO alumnos (nombre, edad, municipio) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $edad, $municipio]);

    header("Location: alumnos.php");
}
?>

