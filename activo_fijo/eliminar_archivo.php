<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

if (isset($_GET['id'])) {
    $id_documento = $_GET['id'];

    try {
        // Conectar a la base de datos
        $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener la ruta del archivo
        $sql = "SELECT ruta_archivo FROM documentos WHERE id_documento = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_documento]);
        $documento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($documento && file_exists($documento['ruta_archivo'])) {
            unlink($documento['ruta_archivo']); // Eliminar el archivo físico
        }

        // Eliminar el registro de la base de datos
        $sqlDelete = "DELETE FROM documentos WHERE id_documento = ?";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([$id_documento]);

        // Redirigir a unidades
        header("Location: unidades.php");
        exit;

    } catch (PDOException $e) {
        die("Error al eliminar el archivo: " . $e->getMessage());
    }
} else {
    die("ID de documento no especificado.");
}
