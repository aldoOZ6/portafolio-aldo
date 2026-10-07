<?php 
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

$msg = ''; // ✅ (AGREGADO)

try {
    $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
    $pdo = new PDO($dsn, $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ (AGREGADO) leer mensaje por URL
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'registrado') $msg = "Torton registrado correctamente.";
        if ($_GET['msg'] === 'eliminado') $msg = "Torton eliminado correctamente.";
    }

    // Registrar torton
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
        $sqlInsert = "INSERT INTO tortones (inventario, descripcion, modelo, num_serie, placas_vehiculo, fecha_adquisicion)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            $_POST['inventario'],
            $_POST['descripcion'],
            $_POST['modelo'],
            $_POST['num_serie'],
            $_POST['placas_vehiculo'],
            $_POST['fecha_adquisicion']
        ]);

        // ✅ (CAMBIADO) redirigir con mensaje
        header("Location: torton.php?msg=registrado");
        exit;
    }

    // Obtener tortones
    $sql = "SELECT * FROM tortones ORDER BY id_torton DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tractos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener documentos
    $sqlDoc = "SELECT * FROM documentos_tortones ORDER BY id_documento DESC";
    $stmtDoc = $pdo->prepare($sqlDoc);
    $stmtDoc->execute();
    $documentos = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

    $documentosPorTracto = [];
    foreach ($documentos as $doc) {
        $documentosPorTracto[$doc['id_torton']][] = $doc;
    }

} catch (PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Tortones Registrados</title>
    <style>
        :root {
            --amarillo-claro: #ffe600ff;
            --amarillo: #ffeb3b;
            --azul: #0550ffff;
            --negro: #000000;
        }

        body {
            font-family: Arial, sans-serif;
            background-image: url('img/moderna1.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: var(--negro);
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--azul);
            text-shadow: 1px 1px 2px var(--negro);
        }

        table {
            width: 100%;
            max-width: 1100px;
            margin: auto;
            border-collapse: collapse;
            background: var(--amarillo-claro);
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        th, td {
            padding: 12px;
            border: 1px solid var(--azul);
            text-align: center;
            font-size: 15px;
            color: var(--negro);
        }

        th {
            background: var(--azul);
            color: var(--amarillo-claro);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:nth-child(even) {
            background-color: var(--amarillo-claro);
        }

        tr:hover {
            background-color: var(--amarillo);
            color: var(--negro);
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
            font-weight: bold;
            color: var(--negro);
            background-color: var(--amarillo);
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease;
            text-shadow: 0 0 1px var(--negro);
        }

        .btn:hover {
            background-color: var(--azul);
            color: var(--amarillo-claro);
        }

        .btn-volver {
            background-color: #ccc;
            color: var(--negro);
            box-shadow: none;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
            display: inline-block;
        }

        .btn-volver:hover {
            background-color: var(--azul);
            color: var(--amarillo-claro);
        }

        .btn-eliminar {
            background-color: #ff6666;
            color: white;
        }

        .btn-eliminar:hover {
            background-color: #cc0000;
        }

        .form-container {
            max-width: 600px;
            margin: 30px auto;
            background: var(--amarillo-claro);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            display: none;
            color: var(--negro);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: var(--azul);
        }

        input[type=text],
        input[type=number],
        input[type=date],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid var(--azul);
            border-radius: 6px;
            font-size: 14px;
            color: var(--negro);
            background-color: var(--amarillo-claro);
        }

        .form-subir {
            max-width: 700px;
            margin: 20px auto;
            background: rgba(255, 225, 0, 0.9);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            color: var(--negro);
            border: 2px solid var(--azul);
        }

        select {
            margin: 10px 0;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid var(--azul);
            font-size: 14px;
            background-color: var(--amarillo-claro);
            color: var(--negro);
        }

        form#formSubirArchivo button {
            background-color: var(--amarillo);
            color: var(--negro);
            font-weight: bold;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease;
            text-shadow: 0 0 1px var(--negro);
        }

        form#formSubirArchivo button:hover {
            background-color: var(--azul);
            color: var(--amarillo-claro);
        }

        /* ✅ botones de archivos (pequeños, una fila, ordenados) */
        .botones-archivos-row{
            margin-top: 6px;
            display:none;
            gap: 8px;
            justify-content:center;
            align-items:center;
            flex-wrap: nowrap;
        }
        .btn-mini{
            display:inline-block;
            width: 95px;
            padding: 6px 0;
            font-size: 13px;
            font-weight: bold;
            border-radius: 10px;
            text-decoration:none;
            text-align:center;
            cursor:pointer;
            border:none;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.3);
        }
        .btn-mini-azul{
            background: var(--azul);
            color: var(--amarillo-claro);
        }
        .btn-mini-azul:hover{
            background: #003dd6;
        }
        .btn-mini-rojo{
            background: #ff4444;
            color: #fff;
        }
        .btn-mini-rojo:hover{
            background: #cc0000;
        }
    </style>
</head>
<body>

<h2>Tortones Registrados</h2>

<?php if ($msg): ?>
    <div style="
        max-width: 900px;
        margin: 0 auto 18px auto;
        padding: 14px 18px;
        border-radius: 10px;
        font-weight: bold;
        text-align: center;
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        box-shadow: 0 0 12px rgba(0,0,0,0.15);
    ">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="text-align:center;">
    <button class="btn" onclick="toggleForm()">Registrar Torton</button>
    <a href="index.php" class="btn btn-volver">Volver al Menú</a>
</div>

<div class="form-container" id="formulario">
    <form method="POST">
        <input type="hidden" name="accion" value="registrar">

        <label>Número de Inventario</label>
        <input type="text" name="inventario" required>

        <label>Descripción</label>
        <input type="text" name="descripcion" required>

        <label>Modelo (Año)</label>
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
    <form id="formSubirArchivo" action="subir_archivo_tortones.php" method="POST" enctype="multipart/form-data">
        <label for="id_torton">Seleccionar Torton:</label>
        <select name="id_torton" required>
            <option value="">-- Selecciona un torton --</option>
            <?php foreach ($tractos as $t): ?>
                <option value="<?= $t['id_torton'] ?>">Torton <?= htmlspecialchars($t['inventario']) ?> - <?= htmlspecialchars($t['descripcion']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="file" name="archivo" accept=".pdf" required />
        <button type="submit">Subir Archivo</button>
    </form>
</div>

<?php if (count($tractos) > 0): ?>
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
            <th>Eliminar Torton</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tractos as $t): ?>
            <tr>
                <td><?= $t['id_torton'] ?></td>
                <td><?= htmlspecialchars($t['inventario']) ?></td>
                <td><?= htmlspecialchars($t['descripcion']) ?></td>
                <td><?= htmlspecialchars($t['modelo']) ?></td>
                <td><?= htmlspecialchars($t['num_serie']) ?></td>
                <td><?= htmlspecialchars($t['placas_vehiculo']) ?></td>
                <td><?= htmlspecialchars($t['fecha_adquisicion']) ?></td>
                <td>
                    <?php if (!empty($documentosPorTracto[$t['id_torton']])): ?>
                        <select onchange="mostrarBotones(this, <?= $t['id_torton'] ?>)" style="background-color: var(--amarillo-claro); color: var(--negro); border: 1px solid var(--azul); border-radius: 6px; padding: 6px; font-weight: bold;">
                            <option value="">-- Selecciona un archivo --</option>
                            <?php foreach ($documentosPorTracto[$t['id_torton']] as $doc): ?>
                                <option value="<?= $doc['id_documento'] ?>"
                                    data-ruta="<?= htmlspecialchars($doc['ruta_archivo']) ?>"
                                    data-nombre="<?= htmlspecialchars($doc['nombre_archivo']) ?>">
                                    <?= htmlspecialchars($doc['nombre_archivo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div id="botones-<?= $t['id_torton'] ?>" class="botones-archivos-row">
                            <a href="#" id="btn-ver-<?= $t['id_torton'] ?>" target="_blank" class="btn-mini btn-mini-azul">Ver</a>
                            <a href="#" id="btn-descargar-<?= $t['id_torton'] ?>" class="btn-mini btn-mini-azul" download>Descargar</a>

                            <!-- ✅ SIN confirm() -->
                            <form method="POST" action="eliminar_archivo_torton.php" style="display:inline; margin:0;">
                                <input type="hidden" name="id_documento" value="">
                                <button type="submit" class="btn-mini btn-mini-rojo">Eliminar</button>
                            </form>
                        </div>

                    <?php else: ?>
                        Sin documentos
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST" action="eliminar_torton.php">
                        <input type="hidden" name="id_torton" value="<?= $t['id_torton'] ?>">
                        <button type="button" class="btn btn-eliminar" onclick="abrirModalEliminarTorton(this.form)">Eliminar Torton</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p style="text-align:center; color:red;">No hay tortones registrados.</p>
<?php endif; ?>

<script>
function toggleForm() {
    const form = document.getElementById('formulario');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

function mostrarBotones(selectElem, idTorton) {
    const botonesDiv = document.getElementById('botones-' + idTorton);
    const btnVer = document.getElementById('btn-ver-' + idTorton);
    const btnDesc = document.getElementById('btn-descargar-' + idTorton);

    if (selectElem.value) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const ruta = selectedOption.getAttribute('data-ruta');
        const idDocumento = selectElem.value;

        btnVer.href = ruta;
        btnDesc.href = ruta;

        const inputEliminar = botonesDiv.querySelector('input[name="id_documento"]');
        inputEliminar.value = idDocumento;

        botonesDiv.style.display = 'flex';
    } else {
        botonesDiv.style.display = 'none';
        btnVer.href = '#';
        btnDesc.href = '#';
        const inputEliminar = botonesDiv.querySelector('input[name="id_documento"]');
        inputEliminar.value = '';
    }
}
</script>

<div id="modalEliminarTorton" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:var(--amarillo-claro); padding:22px 26px; border-radius:12px; text-align:center; box-shadow:0 0 18px rgba(0,0,0,.5);">
    <div style="font-weight:bold; font-size:16px; color:var(--negro); margin-bottom:16px;">
      ¿Deseas eliminar este torton y todos sus archivos?
    </div>
    <button type="button" class="btn btn-eliminar" onclick="confirmarEliminarTorton()">Eliminar</button>
    <button type="button" class="btn btn-volver" onclick="cerrarModalEliminarTorton()">Cancelar</button>
  </div>
</div>

<script>
let formTortonAEliminar = null;

function abrirModalEliminarTorton(form) {
    formTortonAEliminar = form;
    document.getElementById('modalEliminarTorton').style.display = 'flex';
}

function cerrarModalEliminarTorton() {
    document.getElementById('modalEliminarTorton').style.display = 'none';
    formTortonAEliminar = null;
}

function confirmarEliminarTorton() {
    if (formTortonAEliminar) {
        formTortonAEliminar.submit();
    }
}
</script>

</body>
</html>
