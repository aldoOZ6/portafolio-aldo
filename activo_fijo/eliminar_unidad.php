<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

if (isset($_GET['id'])) {
    $id_unidad = $_GET['id'];

    try {
        $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Eliminar unidad
        $sql = "DELETE FROM unidades WHERE id_unidad = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_unidad]);

        // ✅ AQUÍ ESTÁ LA SOLUCIÓN: mandar mensaje de eliminado
        header("Location: unidades.php?msg=eliminado");
        exit;

    } catch (PDOException $e) {
        die("Error al eliminar la unidad: " . $e->getMessage());
    }
} else {
    die("ID de unidad no especificado.");
}
?>
