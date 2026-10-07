<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_documento']) && $_POST['id_documento'] !== '') {

        $id = intval($_POST['id_documento']);

        $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener ruta del archivo
        $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos_tortones WHERE id_documento = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($doc) {
            $ruta = $doc['ruta_archivo'];

            // Eliminar archivo físico
            if ($ruta && file_exists($ruta)) {
                unlink($ruta);
            }

            // Eliminar registro de BD
            $stmt = $pdo->prepare("DELETE FROM documentos_tortones WHERE id_documento = ?");
            $stmt->execute([$id]);
        }

        header("Location: torton.php?msg=archivo_eliminado");
        exit;

    } else {
        echo "ID no recibido.";
    }

} catch (PDOException $e) {
    die("Error al eliminar archivo: " . $e->getMessage());
}
