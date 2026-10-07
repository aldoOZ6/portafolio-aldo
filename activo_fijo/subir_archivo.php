<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["archivo"]) && isset($_POST["id_unidad"])) {
        $id_unidad = $_POST["id_unidad"];
        $archivo = $_FILES["archivo"];

        // Validar tipo
        if ($archivo["type"] != "application/pdf") {
            die("Solo se permiten archivos PDF.");
        }

        // Crear carpeta de documentos si no existe
        $carpetaDestino = "documentos/";
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        // Generar nombre único
        $nombreOriginal = basename($archivo["name"]);
        $nombreArchivo = uniqid() . "_" . $nombreOriginal;
        $rutaArchivo = $carpetaDestino . $nombreArchivo;

        // Mover archivo al servidor
        if (move_uploaded_file($archivo["tmp_name"], $rutaArchivo)) {
            try {
                $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
                $pdo = new PDO($dsn, $usuario, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Insertar en base de datos
                $sql = "INSERT INTO documentos (id_unidad, nombre_archivo, ruta_archivo, fecha_subida)
                        VALUES (?, ?, ?, CURDATE())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_unidad, $nombreOriginal, $rutaArchivo]);

                header("Location: unidades.php");
                exit;
            } catch (PDOException $e) {
                die("Error en la base de datos: " . $e->getMessage());
            }
        } else {
            die("Error al subir el archivo.");
        }
    } else {
        die("Faltan datos del formulario.");
    }
} else {
    die("Acceso no permitido.");
}
