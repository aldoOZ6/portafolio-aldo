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

    // Consulta de datos
    $stmt = $pdo->query("SELECT * FROM alumnos");
    $alumnos = $stmt->fetchAll();

    if (empty($alumnos)) {
        echo "No hay alumnos registrados.";
        exit;
    }

    // Cabeceras para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="alumnos.csv"');

    // Evitar errores de codificación en Excel
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // Encabezados
    fputcsv($output, array_keys($alumnos[0]));

    // Filas
    foreach ($alumnos as $alumno) {
        fputcsv($output, $alumno);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    echo "Error al conectar con la base de datos: " . $e->getMessage();
}
?>
