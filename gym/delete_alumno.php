<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_alumno"])) {
    $id_alumno = $_POST["id_alumno"];

    try {
        $conn->beginTransaction();

        // Primero eliminar asistencias asociadas (si tienes esta tabla y FK)
        $stmt = $conn->prepare("DELETE FROM asistencias WHERE id_alumno = :id_alumno");
        $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt->execute();

        // Luego eliminar alumno
        $stmt = $conn->prepare("DELETE FROM alumnos WHERE id_alumno = :id_alumno");
        $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit();

        header("Location: alumnos.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al eliminar alumno: " . $e->getMessage();
    }
} else {
    echo "No se recibió un ID válido.";
}
?>
