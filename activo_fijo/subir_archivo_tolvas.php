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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['id_tolva']) && isset($_FILES['archivo'])) {
            $id_tolva = $_POST['id_tolva'];
            $archivo = $_FILES['archivo'];

            if ($archivo['type'] === 'application/pdf') {
                $nombre_archivo = basename($archivo['name']);
                $ruta = "documentos_tolvas/" . time() . "_" . $nombre_archivo;

                if (!file_exists('documentos_tolvas')) {
                    mkdir('documentos_tolvas', 0777, true);
                }

                if (move_uploaded_file($archivo['tmp_name'], $ruta)) {
                    $sqlInsert = "INSERT INTO documentos_tolvas (id_tolva, nombre_archivo, ruta_archivo) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sqlInsert);
                    $stmt->execute([$id_tolva, $nombre_archivo, $ruta]);

                    header("Location: tolva.php?msg=archivo_subido");
                    exit;
                } else {
                    header("Location: tolva.php?msg=error_subida");
                    exit;
                }
            } else {
                header("Location: tolva.php?msg=error_pdf");
                exit;
            }
        }
    }

} catch (PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}
?>
