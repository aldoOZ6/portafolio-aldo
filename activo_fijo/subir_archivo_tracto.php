<?php
// Configuración de la base de datos
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

try {
    $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
    $pdo = new PDO($dsn, $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica si se envió un archivo y un id_tracto
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo']) && isset($_POST['id_tracto'])) {
        $idTracto = $_POST['id_tracto'];
        $archivo = $_FILES['archivo'];

        // Verifica que no haya errores al subir el archivo
        if ($archivo['error'] === 0) {
            $nombreArchivo = basename($archivo['name']);
            $nombreUnico = uniqid() . "_" . $nombreArchivo;
            $rutaDestino = "archivos_tractos/" . $nombreUnico;

            // Crea el directorio si no existe
            if (!file_exists("archivos_tractos")) {
                mkdir("archivos_tractos", 0777, true);
            }

            // Mueve el archivo a la carpeta de destino
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                // Inserta los datos en la tabla documentos_tractos
                $sql = "INSERT INTO documentos_tractos (id_tracto, nombre_archivo, ruta_archivo) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idTracto, $nombreArchivo, $rutaDestino]);

                header("Location: tractos.php");
                exit;
            } else {
                echo "Error al mover el archivo al directorio destino.";
            }
        } else {
            echo "Error al subir el archivo. Código de error: " . $archivo['error'];
        }
    } else {
        echo "Faltan datos: archivo o id de tracto no recibido.";
    }

} catch (PDOException $e) {
    die("Error en la conexión o inserción: " . $e->getMessage());
}
?>
