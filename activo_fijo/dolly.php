<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

$msg = '';

try {
    $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
    $pdo = new PDO($dsn, $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === 'registrar_dolly') {
        $sqlInsert = "INSERT INTO dollys (inventario, descripcion, modelo, num_serie, placas_vehiculo, fecha_adquisicion)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([
            $_POST['inventario'],
            $_POST['descripcion'],
            $_POST['modelo'],
            $_POST['num_serie'],
            $_POST['placas_vehiculo'],
            $_POST['fecha_adquisicion']
        ]);
        $msg = "Dolly registrado correctamente.";
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === 'subir_archivo') {
        if (isset($_POST['id_dolly']) && isset($_FILES['archivo'])) {
            $idDolly = $_POST['id_dolly'];
            $archivo = $_FILES['archivo'];

            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $msg = "Solo se permiten archivos PDF.";
            } else {
                $carpeta = "archivos_dollys/";
                if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

                $nombreFinal = uniqid() . "_" . basename($archivo['name']);
                $rutaFinal = $carpeta . $nombreFinal;

                if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
                    $sql = "INSERT INTO documentos_dollys (id_dolly, nombre_archivo, ruta_archivo) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idDolly, $archivo['name'], $rutaFinal]);
                    $msg = "Archivo subido correctamente.";
                } else {
                    $msg = "Error al subir el archivo.";
                }
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === 'eliminar_archivo') {
        $idDoc = $_POST['id_documento'];
        $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos_dollys WHERE id_documento = ?");
        $stmt->execute([$idDoc]);
        $archivo = $stmt->fetch();

        if ($archivo && file_exists($archivo['ruta_archivo'])) {
            unlink($archivo['ruta_archivo']);
        }

        $stmt = $pdo->prepare("DELETE FROM documentos_dollys WHERE id_documento = ?");
        $stmt->execute([$idDoc]);

        $msg = "Archivo eliminado correctamente.";
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === 'eliminar_dolly') {
        $idDolly = $_POST['id_dolly'];

        $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos_dollys WHERE id_dolly = ?");
        $stmt->execute([$idDolly]);
        $archivos = $stmt->fetchAll();

        foreach ($archivos as $a) {
            if (file_exists($a['ruta_archivo'])) unlink($a['ruta_archivo']);
        }

        $pdo->prepare("DELETE FROM documentos_dollys WHERE id_dolly = ?")->execute([$idDolly]);
        $pdo->prepare("DELETE FROM dollys WHERE id_dolly = ?")->execute([$idDolly]);

        $msg = "Dolly y sus archivos fueron eliminados.";
    }

    $dollys = $pdo->query("SELECT * FROM dollys ORDER BY id_dolly DESC")->fetchAll(PDO::FETCH_ASSOC);
    $documentos = $pdo->query("SELECT * FROM documentos_dollys ORDER BY id_documento DESC")->fetchAll(PDO::FETCH_ASSOC);

    $documentosPorDolly = [];
    foreach ($documentos as $doc) {
        $documentosPorDolly[$doc['id_dolly']][] = $doc;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dollys Registrados</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 20px;
            background: url('img/moderna1.jpg') no-repeat center center fixed;
            background-size: cover;
            color: black;
        }
        h2 {
            text-align: center;
            color: black;
            text-shadow: 1px 1px 2px #fff000;
        }
        table {
            width: 100%;
            max-width: 1100px;
            margin: auto;
            border-collapse: collapse;
            background: rgba(255, 255, 153, 0.9);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #ffff00;
            color: black;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
            background: #ffff00;
            color: black;
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: rgba(255, 255, 153, 0.95);
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px #ccc;
            display: none;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: black;
        }
        input[type=text],
        input[type=number],
        input[type=date],
        select,
        input[type=file] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-subir {
            max-width: 600px;
            margin: 20px auto;
            background: rgba(255, 255, 153, 0.95);
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px #ccc;
            text-align: center;
        }

        /* ✅ SOLO AGREGADO: BOTONES EN FILA, MAS CHICOS, MISMO TAMAÑO (TABLA ARCHIVOS) */
        .botones-archivos{
            margin-top: 6px;
            display:none;
            gap: 6px;
            justify-content:center;
            align-items:center;
            flex-wrap: nowrap;
        }
        .btn-mini{
            padding: 6px 10px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration:none;
            display:inline-block;
            min-width: 92px;     /* mismo tamaño */
            text-align:center;
            cursor:pointer;
            border:none;
        }
        .btn-mini-ver, .btn-mini-desc{
            background: #007bff;
            color:#fff;
        }
        .btn-mini-del{
            background: #dc3545;
            color:#fff;
        }
    </style>
</head>
<body>

<h2>Dollys Registrados</h2>

<?php if ($msg): ?>
    <div style="max-width:600px; margin: 10px auto; padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; text-align:center;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="text-align:center;">
    <button class="btn" onclick="toggleForm()">Registrar Dolly</button>
    <a href="index.php" class="btn">Volver al Menú</a>
</div>

<div class="form-container" id="formulario">
    <form method="POST">
        <input type="hidden" name="accion" value="registrar_dolly">
        <label>Inventario</label>
        <input type="text" name="inventario" required>
        <label>Descripción</label>
        <input type="text" name="descripcion" required>
        <label>Modelo</label>
        <input type="number" name="modelo">
        <label>Número de Serie</label>
        <input type="text" name="num_serie" required>
        <label>Placas del Vehículo</label>
        <input type="text" name="placas_vehiculo">
        <label>Fecha de Adquisición</label>
        <input type="date" name="fecha_adquisicion">
        <br><br>
        <button type="submit" class="btn">Registrar</button>
    </form>
</div>

<div class="form-subir">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="subir_archivo">
        <label>Selecciona un Dolly:</label>
        <select name="id_dolly" required>
            <option value="">-- Elegir Dolly --</option>
            <?php foreach ($dollys as $d): ?>
                <option value="<?= $d['id_dolly'] ?>"><?= htmlspecialchars($d['inventario']) ?> - <?= htmlspecialchars($d['descripcion']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="file" name="archivo" accept=".pdf" required>
        <button type="submit" class="btn">Subir Archivo</button>
    </form>
</div>

<?php if (count($dollys) > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Inventario</th>
            <th>Descripción</th>
            <th>Modelo</th>
            <th>Serie</th>
            <th>Placas</th>
            <th>Fecha Adq.</th>
            <th>Archivos</th>
            <th>Eliminar Dolly</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dollys as $d): ?>
        <tr>
            <td><?= $d['id_dolly'] ?></td>
            <td><?= htmlspecialchars($d['inventario']) ?></td>
            <td><?= htmlspecialchars($d['descripcion']) ?></td>
            <td><?= htmlspecialchars($d['modelo']) ?></td>
            <td><?= htmlspecialchars($d['num_serie']) ?></td>
            <td><?= htmlspecialchars($d['placas_vehiculo']) ?></td>
            <td><?= htmlspecialchars($d['fecha_adquisicion']) ?></td>

            <td>
                <?php if (!empty($documentosPorDolly[$d['id_dolly']])): ?>
                    <select onchange="mostrarBotones(this, <?= $d['id_dolly'] ?>)">
                        <option value="">-- Selecciona un archivo --</option>
                        <?php foreach ($documentosPorDolly[$d['id_dolly']] as $doc): ?>
                            <option value="<?= $doc['id_documento'] ?>"
                                    data-ruta="<?= htmlspecialchars($doc['ruta_archivo']) ?>">
                                <?= htmlspecialchars($doc['nombre_archivo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- ✅ BOTONES EN ORDEN: VER / DESCARGAR / ELIMINAR -->
                    <div id="botones-<?= $d['id_dolly'] ?>" class="botones-archivos">
                        <a href="#" id="btn-ver-<?= $d['id_dolly'] ?>" target="_blank" class="btn-mini btn-mini-ver">Ver</a>
                        <a href="#" id="btn-descargar-<?= $d['id_dolly'] ?>" download class="btn-mini btn-mini-desc">Descargar</a>

                        <!-- ✅ AQUÍ SE QUITÓ EL CONFIRM PARA QUE NO SALGA EL MENSAJE -->
                        <form method="POST" style="display:inline; margin:0;">
                            <input type="hidden" name="accion" value="eliminar_archivo">
                            <input type="hidden" name="id_documento" value="">
                            <button type="submit" class="btn-mini btn-mini-del">Eliminar</button>
                        </form>
                    </div>

                <?php else: ?>
                    Sin documentos
                <?php endif; ?>
            </td>

            <td>
                <form method="POST" onsubmit="return confirm('¿Eliminar Dolly y sus archivos?');">
                    <input type="hidden" name="accion" value="eliminar_dolly">
                    <input type="hidden" name="id_dolly" value="<?= $d['id_dolly'] ?>">
                    <button type="submit" class="btn">Eliminar Dolly</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p style="text-align:center; color:red;">No hay dollys registrados.</p>
<?php endif; ?>

<script>
function toggleForm() {
    const form = document.getElementById('formulario');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

function mostrarBotones(select, id) {
    const botones = document.getElementById('botones-' + id);
    const btnVer = document.getElementById('btn-ver-' + id);
    const btnDesc = document.getElementById('btn-descargar-' + id);
    const inputEliminar = botones.querySelector('input[name="id_documento"]');

    if (select.value) {
        const ruta = select.options[select.selectedIndex].getAttribute('data-ruta');

        btnVer.href = ruta;
        btnDesc.href = ruta;

        inputEliminar.value = select.value;
        botones.style.display = 'flex';
    } else {
        botones.style.display = 'none';
        btnVer.href = '#';
        btnDesc.href = '#';
        inputEliminar.value = '';
    }
}
</script>

</body>
</html>


