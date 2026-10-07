<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_torton'])) {
    $idTorton = $_POST['id_torton'];

    try {
        $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 🔹 Obtener archivos del torton
        $sqlArchivos = "SELECT ruta_archivo FROM documentos_tortones WHERE id_torton = ?";
        $stmtArchivos = $pdo->prepare($sqlArchivos);
        $stmtArchivos->execute([$idTorton]);
        $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);

        // 🔹 Borrar archivos físicos
        foreach ($archivos as $archivo) {
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']);
            }
        }

        // 🔹 Borrar registros de documentos
        $sqlDeleteDocs = "DELETE FROM documentos_tortones WHERE id_torton = ?";
        $stmtDeleteDocs = $pdo->prepare($sqlDeleteDocs);
        $stmtDeleteDocs->execute([$idTorton]);

        // 🔹 Borrar torton
        $sqlDeleteTorton = "DELETE FROM tortones WHERE id_torton = ?";
        $stmtDeleteTorton = $pdo->prepare($sqlDeleteTorton);
        $stmtDeleteTorton->execute([$idTorton]);

        // ✅ REDIRECCIÓN CON MENSAJE (ESTO ES LO QUE FALTABA)
        header("Location: torton.php?msg=eliminado");
        exit;

    } catch (PDOException $e) {
        die("Error al eliminar el torton: " . $e->getMessage());
    }
} else {
    header("Location: torton.php");
    exit;
}
