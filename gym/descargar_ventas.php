<?php
$host = 'localhost';
$port = 3307;
$db = 'gym_temoaya';
$user = 'root';
$pass = ''; // Sin contraseña
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Asegurarse de que la conexión esté usando utf8mb4
    $pdo->exec("SET NAMES 'utf8mb4'");

    $stmt = $pdo->query("SELECT * FROM ventas");
    $ventas = $stmt->fetchAll();

    if (empty($ventas)) {
        echo "No hay ventas registradas.";
        exit;
    }

    // Preparar descarga CSV con encabezados adecuados
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="ventas.csv"');

    $output = fopen('php://output', 'w');

    // Escribir una BOM para garantizar la correcta codificación de caracteres (UTF-8)
    fwrite($output, "\xEF\xBB\xBF");

    // Escribir encabezados
    fputcsv($output, array_keys($ventas[0]));

    // Escribir cada fila
    foreach ($ventas as $venta) {
        fputcsv($output, $venta);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    echo "Error al conectar con la base de datos: " . $e->getMessage();
}
?>
