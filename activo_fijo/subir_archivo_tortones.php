<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

try {
    $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
    $pdo = new PDO($dsn, $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_torton']) && isset($_FILES['archivo'])) {
        $idTorton = intval($_POST['id_torton']); // aseguramos que sea entero
        $archivo = $_FILES['archivo'];

        if ($archivo['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $allowedMimes = ['application/pdf'];

            if ($ext === 'pdf' && in_array($archivo['type'], $allowedMimes)) {
                $nombreArchivo = uniqid('torton_') . '_' . basename($archivo['name']);

                if (!is_dir(__DIR__ . "/archivos_torton")) {
                    mkdir(__DIR__ . "/archivos_torton", 0777, true);
                }

                $rutaDestino = __DIR__ . "/archivos_torton/" . $nombreArchivo;
                $rutaBD = "archivos_torton/" . $nombreArchivo; // ruta relativa para la base de datos

                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $sql = "INSERT INTO documentos_tortones (id_torton, nombre_archivo, ruta_archivo) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idTorton, $archivo['name'], $rutaBD]);

                    header("Location: torton.php");
                    exit;
                } else {
                    echo "Error al mover el archivo.";
                }
            } else {
                echo "Archivo inválido (debe ser PDF) o tipo MIME incorrecto.";
            }
        } else {
            echo "Error en la carga del archivo. Código de error: " . $archivo['error'];
        }
    } else {
        echo "Datos no válidos.";
    }

} catch (PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}
