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

    // Configura correctamente la codificación en la conexión.
    $pdo->exec("SET NAMES 'utf8mb4'");

    date_default_timezone_set('America/Mexico_City');
    $hoy = date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT alumnos.nombre, asistencias.fecha, asistencias.asistencia 
        FROM asistencias 
        INNER JOIN alumnos ON asistencias.id_alumno = alumnos.id_alumno 
        WHERE asistencias.fecha = ?");
    $stmt->execute([$hoy]);
    $asistencias = $stmt->fetchAll();

    if (empty($asistencias)) {
        echo "No hay asistencias registradas para hoy.";
        exit;
    }

    // Configura la codificación UTF-8 para el archivo CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="asistencias_' . $hoy . '.csv"');

    $output = fopen('php://output', 'w');
    
    // Escribir una BOM (Byte Order Mark) para asegurar que Excel y otros programas reconozcan UTF-8
    fwrite($output, "\xEF\xBB\xBF");

    // Escribir encabezados
    fputcsv($output, array('Nombre del Alumno', 'Fecha', 'Asistencia'));

    // Escribir los registros
    foreach ($asistencias as $asistencia) {
        fputcsv($output, $asistencia);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>
