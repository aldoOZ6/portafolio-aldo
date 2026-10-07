<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

try {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener ruta del archivo
        $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos_tractos WHERE id_documento = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($doc && file_exists($doc['ruta_archivo'])) {
            unlink($doc['ruta_archivo']); // eliminar archivo físico
        }

        // Eliminar registro de base de datos
        $stmt = $pdo->prepare("DELETE FROM documentos_tractos WHERE id_documento = ?");
        $stmt->execute([$id]);

        header("Location: tractos.php");
        exit;
    } else {
        echo "ID no recibido.";
    }

} catch (PDOException $e) {
    die("Error al eliminar archivo: " . $e->getMessage());
}
?>
