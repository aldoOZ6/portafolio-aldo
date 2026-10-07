<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

try {
    $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
    $pdo = new PDO($dsn, $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $inventario = $_POST['inventario'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $num_serie = $_POST['num_serie'] ?? '';
    $fecha_adquisicion = $_POST['fecha_adquisicion'] ?? null;
    $placas_vehiculo = $_POST['placas_vehiculo'] ?? '';
    $factura = $_POST['factura'] ?? '';
    $tjc = $_POST['tjc'] ?? '';
    $poliza_seguro = $_POST['poliza_seguro'] ?? '';
    $inciso = $_POST['inciso'] ?? '';

    if (!$inventario || !$descripcion || !$modelo || !$num_serie || !$fecha_adquisicion) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
    } else {
        try {
            $sql = "INSERT INTO unidades (inventario, descripcion, modelo, num_serie, fecha_adquisicion, placas_vehiculo, factura, tjc, poliza_seguro, inciso)
                    VALUES (:inventario, :descripcion, :modelo, :num_serie, :fecha_adquisicion, :placas_vehiculo, :factura, :tjc, :poliza_seguro, :inciso)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':inventario' => $inventario,
                ':descripcion' => $descripcion,
                ':modelo' => $modelo,
                ':num_serie' => $num_serie,
                ':fecha_adquisicion' => $fecha_adquisicion,
                ':placas_vehiculo' => $placas_vehiculo,
                ':factura' => $factura,
                ':tjc' => $tjc,
                ':poliza_seguro' => $poliza_seguro,
                ':inciso' => $inciso
            ]);

            $id_unidad = $pdo->lastInsertId();

            if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
                $carpeta = "archivos/";
                if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

                $nombreArchivo = basename($_FILES['archivo_pdf']['name']);
                $rutaDestino = $carpeta . $nombreArchivo;

                if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $rutaDestino)) {
                    $sqlDoc = "INSERT INTO documentos (id_unidad, tipo_documento, nombre_archivo, ruta_archivo, fecha_subida)
                               VALUES (:id_unidad, :tipo_documento, :nombre_archivo, :ruta_archivo, CURDATE())";
                    $stmtDoc = $pdo->prepare($sqlDoc);
                    $stmtDoc->execute([
                        ':id_unidad' => $id_unidad,
                        ':tipo_documento' => 'Documento PDF',
                        ':nombre_archivo' => $nombreArchivo,
                        ':ruta_archivo' => $rutaDestino
                    ]);
                } else {
                    $mensaje = "Error al subir el archivo PDF.";
                }
            }

            if (!$mensaje) {
                // Redirigir a la lista con mensaje
                header("Location: unidades.php?msg=guardado");
                exit;
            }

        } catch (PDOException $e) {
            $mensaje = "Error al guardar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Caja Seca</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        form {
            max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 15px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer;}
        button:hover { background: #0056b3; }
        .mensaje {
            max-width: 600px; margin: 15px auto; padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;
            text-align: center;
        }
        .error {
            background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
        }
        .btn-volver {
            display: block; width: 160px; margin: 20px auto; padding: 10px;
            background-color: #007bff; color: white; text-align: center;
            text-decoration: none; border-radius: 5px;
        }
        .btn-volver:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h2>Registrar Caja Seca</h2>

<?php if ($mensaje): ?>
    <div class="mensaje <?= strpos($mensaje, 'Error') !== false ? 'error' : '' ?>">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<form action="registro_unidad.php" method="post" enctype="multipart/form-data">
    <label for="inventario">Número de Inventario *</label>
    <input type="text" id="inventario" name="inventario" required>

    <label for="descripcion">Descripción *</label>
    <input type="text" id="descripcion" name="descripcion" required>

    <label for="modelo">Modelo (Año) *</label>
    <input type="number" id="modelo" name="modelo" min="1900" max="2100" required>

    <label for="num_serie">Número de Serie *</label>
    <input type="text" id="num_serie" name="num_serie" required>

    <label for="fecha_adquisicion">Fecha de Adquisición *</label>
    <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" required>

    <label for="placas_vehiculo">Número de Placa de Vehículo</label>
    <input type="text" id="placas_vehiculo" name="placas_vehiculo">

    <label for="factura">Factura (FAC)</label>
    <input type="text" id="factura" name="factura">

    <label for="tjc">TJC</label>
    <input type="text" id="tjc" name="tjc">

    <label for="poliza_seguro">Póliza de Seguro</label>
    <input type="text" id="poliza_seguro" name="poliza_seguro">

    <label for="inciso">Inciso</label>
    <input type="text" id="inciso" name="inciso">

    <label for="archivo_pdf">Subir archivo PDF</label>
    <input type="file" id="archivo_pdf" name="archivo_pdf" accept="application/pdf">

    <button type="submit" name="guardar">Registrar</button>
</form>

<a href="unidades.php" class="btn-volver">Volver a la lista</a>

</body>
</html>
