<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_documento'])) {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=activo_fijo;charset=utf8", "root", "");
    $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos_tolvas WHERE id_documento = ?");
    $stmt->execute([$_POST['id_documento']]);
    $archivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($archivo && file_exists($archivo['ruta_archivo'])) {
        unlink($archivo['ruta_archivo']);
    }

    $stmt = $pdo->prepare("DELETE FROM documentos_tolvas WHERE id_documento = ?");
    $stmt->execute([$_POST['id_documento']]);

    header("Location: tolva.php?msg=archivo_eliminado");
}
?>
