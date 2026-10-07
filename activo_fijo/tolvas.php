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

    // Registrar tolva
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
        $sqlInsert = "INSERT INTO tolvas (inventario, descripcion, modelo, num_serie, placas_vehiculo, fecha_adquisicion)
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
        $msg = "Tolva registrada correctamente.";
    }

    // Subir archivo
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'subir_archivo') {
        if (isset($_POST['id_tolva']) && isset($_FILES['archivo'])) {
            $idTolva = $_POST['id_tolva'];
            $archivo = $_FILES['archivo'];

            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $msg = "Error: Solo se permiten archivos PDF.";
            } else {
                $carpetaDestino = "archivos_tolvas/";
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }

                $nombreArchivo = uniqid() . "_" . basename($archivo['name']);
                $rutaDestino = $carpetaDestino . $nombreArchivo;

                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $sqlInsertDoc = "INSERT INTO documentos_tolvas (id_tolva, nombre_archivo, ruta_archivo) VALUES (?, ?, ?)";
                    $stmtInsertDoc = $pdo->prepare($sqlInsertDoc);
                    $stmtInsertDoc->execute([$idTolva, $archivo['name'], $rutaDestino]);
                    $msg = "Archivo subido correctamente.";
                } else {
                    $msg = "Error al subir el archivo.";
                }
            }
        }
    }

    // Eliminar archivo
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_archivo') {
        if (isset($_POST['id_documento']) && $_POST['id_documento'] !== '') {
            $idDocumento = $_POST['id_documento'];

            $sqlGet = "SELECT ruta_archivo FROM documentos_tolvas WHERE id_documento = ?";
            $stmtGet = $pdo->prepare($sqlGet);
            $stmtGet->execute([$idDocumento]);
            $archivo = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if ($archivo) {
                if (file_exists($archivo['ruta_archivo'])) {
                    unlink($archivo['ruta_archivo']);
                }

                $sqlDelete = "DELETE FROM documentos_tolvas WHERE id_documento = ?";
                $stmtDelete = $pdo->prepare($sqlDelete);
                $stmtDelete->execute([$idDocumento]);

                $msg = "Archivo eliminado correctamente.";
            } else {
                $msg = "No se encontró el archivo para eliminar.";
            }
        } else {
            $msg = "No se recibió ID del documento.";
        }
    }

    // Eliminar tolva (y sus archivos)
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_tolva') {
        if (isset($_POST['id_tolva'])) {
            $idTolva = $_POST['id_tolva'];

            $sqlFiles = "SELECT ruta_archivo FROM documentos_tolvas WHERE id_tolva = ?";
            $stmtFiles = $pdo->prepare($sqlFiles);
            $stmtFiles->execute([$idTolva]);
            $archivos = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

            foreach ($archivos as $archivo) {
                if (file_exists($archivo['ruta_archivo'])) {
                    unlink($archivo['ruta_archivo']);
                }
            }

            $sqlDeleteDocs = "DELETE FROM documentos_tolvas WHERE id_tolva = ?";
            $stmtDeleteDocs = $pdo->prepare($sqlDeleteDocs);
            $stmtDeleteDocs->execute([$idTolva]);

            $sqlDeleteTolva = "DELETE FROM tolvas WHERE id_tolva = ?";
            $stmtDeleteTolva = $pdo->prepare($sqlDeleteTolva);
            $stmtDeleteTolva->execute([$idTolva]);

            $msg = "Tolva eliminada correctamente.";
        }
    }

    // Obtener tolvas
    $sql = "SELECT * FROM tolvas ORDER BY id_tolva DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tolvas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener documentos
    $sqlDoc = "SELECT * FROM documentos_tolvas ORDER BY id_documento DESC";
    $stmtDoc = $pdo->prepare($sqlDoc);
    $stmtDoc->execute();
    $documentos = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

    $documentosPorTolva = [];
    foreach ($documentos as $doc) {
        $documentosPorTolva[$doc['id_tolva']][] = $doc;
    }

} catch (PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tolvas Registradas</title>

<style>
:root {
    --amarillo: #fbff00ff;
    --negro: #000000;
    --azul: #007bff;
    --azul-oscuro: #0056b3;
    --rojo: #dc3545;
    --rojo-oscuro: #a71d2a;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('img/moderna1.jpg') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 30px 20px;
    color: var(--negro);
    background-color: var(--amarillo);
}

h2 {
    text-align: center;
    font-weight: 900;
    font-size: 2.5rem;
    color: var(--amarillo);
    text-shadow: 1px 1px 2px var(--negro);
    margin-bottom: 30px;
}

table {
    width: 100%;
    max-width: 1100px;
    margin: auto;
    border-collapse: collapse;
    background: var(--amarillo);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0,0,0,0.4);
    color: var(--negro);
    font-weight: 700;
}

th, td {
    padding: 12px 10px;
    border: 1px solid var(--negro);
    text-align: center;
    font-size: 1rem;
    vertical-align: middle;
    color: var(--negro);
}

thead {
    background-color: var(--azul);
    color: var(--amarillo);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    font-size: 1.1rem;
}

tbody tr:nth-child(even),
tbody tr {
    background-color: var(--amarillo);
    color: var(--negro);
}

.btn {
    padding: 10px 16px;
    border-radius: 8px;
    border: none;
    font-weight: 900;
    font-size: 1rem;
    cursor: pointer;
    margin: 5px 8px;
    user-select: none;
    box-shadow: 2px 2px 8px rgba(0,0,0,0.3);
    transition: background-color 0.3s ease, color 0.3s ease;
    text-decoration: none;
    display: inline-block;
    color: var(--negro);
}

.btn-registrar {
    background-color: var(--amarillo);
    color: var(--negro);
    border: 2px solid var(--negro);
    text-shadow: 0 0 1px var(--negro);
}
.btn-registrar:hover {
    background-color: var(--negro);
    color: var(--amarillo);
    border-color: var(--amarillo);
    box-shadow: 0 0 15px var(--amarillo);
}

.btn-volver {
    background-color: var(--azul);
    color: var(--amarillo);
    border: 2px solid var(--azul);
}
.btn-volver:hover {
    background-color: var(--azul-oscuro);
    border-color: var(--azul-oscuro);
    box-shadow: 0 0 15px var(--azul-oscuro);
}

.btn-eliminar {
    background-color: var(--azul);
    color: var(--amarillo);
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 900;
    border: none;
    box-shadow: 0 5px 15px var(--azul-oscuro);
    transition: background-color 0.3s ease;
}
.btn-eliminar:hover {
    background-color: var(--amarillo);
    color: var(--azul);
    box-shadow: 0 0 20px var(--amarillo);
}

.form-container {
    max-width: 600px;
    margin: 20px auto 40px;
    background: var(--amarillo);
    padding: 25px 30px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
    color: var(--negro);
    font-weight: 700;
    display: none;
}

label {
    display: block;
    margin-top: 15px;
    font-size: 1.1rem;
    color: var(--negro);
}

input[type=text], input[type=number], input[type=date], select, input[type=file] {
    width: 100%;
    padding: 10px 14px;
    margin-top: 6px;
    border: 2px solid var(--negro);
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    color: var(--negro);
    background: var(--amarillo);
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    outline-offset: 2px;
}

.form-container button, .form-subir button {
    width: 100%;
    padding: 16px 0;
    font-size: 1.3rem;
    background-color: var(--negro);
    color: var(--amarillo);
    border: 2px solid var(--amarillo);
    border-radius: 15px;
    cursor: pointer;
    font-weight: 900;
    box-shadow: 0 6px 18px rgba(0,0,0,0.7);
    transition: background-color 0.3s ease, color 0.3s ease;
}
.form-container button:hover, .form-subir button:hover {
    background-color: var(--amarillo);
    color: var(--negro);
    box-shadow: 0 0 22px var(--amarillo);
}

.form-subir {
    max-width: 600px;
    margin: 20px auto 40px;
    background: var(--amarillo);
    padding: 25px 30px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
    color: var(--negro);
    text-align: center;
    font-weight: 700;
}

.msg {
    max-width: 600px;
    margin: 15px auto;
    padding: 12px 18px;
    border-radius: 8px;
    font-weight: 900;
    text-align: center;
    box-shadow: 0 0 12px rgba(0,0,0,0.15);
    color: var(--negro);
}
.msg-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.no-data {
    text-align: center;
    font-size: 1.2rem;
    font-weight: 900;
    color: var(--azul);
    margin-top: 50px;
    text-shadow: 0 0 5px var(--azul-oscuro);
}

/* Botones archivos */
.archivos-box{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:8px;
}
.archivos-box select{
    width: 90%;
    max-width: 280px;
    padding: 6px 10px;
    border-radius: 12px;
    border: 2px solid var(--negro);
    font-weight: 900;
}
.archivos-acciones{
    display:flex;
    gap:10px;
    justify-content:center;
    align-items:center;
    flex-wrap:nowrap;
}

.archivos-btn{
    width: auto;
    min-width: 90px;
    padding: 7px 12px;
    font-size: 14px;
    border-radius: 12px;
    margin: 0;
    text-align:center;
    text-decoration:none;
    display:inline-block;
    font-weight:900;
    cursor:pointer;
}

.archivos-btn.ver, .archivos-btn.desc{
    background: var(--azul);
    color: white;
    border: none;
    box-shadow: 0 6px 18px rgba(0,0,0,0.30);
}
.archivos-btn.ver:hover, .archivos-btn.desc:hover{
    background: var(--azul-oscuro);
}
.archivos-btn.del{
    background: var(--rojo);
    color: white;
    border: none;
    box-shadow: 0 6px 18px rgba(0,0,0,0.30);
}
.archivos-btn.del:hover{
    background: var(--rojo-oscuro);
}
</style>

</head>
<body>

<h2>Tolvas Registradas</h2>

<?php if ($msg): ?>
    <div class="msg msg-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div style="text-align:center;">
    <button class="btn btn-registrar" onclick="toggleForm()">Registrar Tolva</button>
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
        <button type="submit" class="btn btn-registrar">Registrar</button>
    </form>
</div>

<div class="form-subir">
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="subir_archivo">
        <label for="id_tolva">Seleccionar Tolva:</label>
        <select name="id_tolva" required>
            <option value="">-- Selecciona una tolva --</option>
            <?php foreach ($tolvas as $t): ?>
                <option value="<?= $t['id_tolva'] ?>">Tolva <?= htmlspecialchars($t['inventario']) ?> - <?= htmlspecialchars($t['descripcion']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="file" name="archivo" accept=".pdf" required />
        <button type="submit" class="btn btn-registrar">Subir Archivo</button>
    </form>
</div>

<?php if (count($tolvas) > 0): ?>
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
            <th>Eliminar Tolva</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tolvas as $t): ?>
            <tr>
                <td><?= $t['id_tolva'] ?></td>
                <td><?= htmlspecialchars($t['inventario']) ?></td>
                <td><?= htmlspecialchars($t['descripcion']) ?></td>
                <td><?= htmlspecialchars($t['modelo']) ?></td>
                <td><?= htmlspecialchars($t['num_serie']) ?></td>
                <td><?= htmlspecialchars($t['placas_vehiculo']) ?></td>
                <td><?= htmlspecialchars($t['fecha_adquisicion']) ?></td>

                <td>
                    <?php if (!empty($documentosPorTolva[$t['id_tolva']])): ?>
                        <div class="archivos-box">
                            <select id="selPdf<?= (int)$t['id_tolva'] ?>" onchange="actualizarArchivo(<?= (int)$t['id_tolva'] ?>)">
                                <?php foreach ($documentosPorTolva[$t['id_tolva']] as $doc): ?>
                                    <option value="<?= htmlspecialchars($doc['ruta_archivo']) ?>" data-id="<?= (int)$doc['id_documento'] ?>">
                                        <?= htmlspecialchars($doc['nombre_archivo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="archivos-acciones">
                                <button type="button" class="archivos-btn ver" onclick="verPdf(<?= (int)$t['id_tolva'] ?>)">Ver</button>
                                <a id="btnDesc<?= (int)$t['id_tolva'] ?>" class="archivos-btn desc" href="#" download>Descargar</a>

                                <!-- ✅ AQUI SE QUITO EL confirm() PARA QUE NO SALGA EL MENSAJE -->
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="accion" value="eliminar_archivo">
                                    <input type="hidden" name="id_documento" id="delDoc<?= (int)$t['id_tolva'] ?>" value="">
                                    <button type="submit" class="archivos-btn del">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        No hay archivos
                    <?php endif; ?>
                </td>

                <td>
                    <form method="POST">
                        <input type="hidden" name="accion" value="eliminar_tolva">
                        <input type="hidden" name="id_tolva" value="<?= (int)$t['id_tolva'] ?>">
                        <button type="button" class="btn btn-eliminar" onclick="abrirModalEliminarTolva(this.form)">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="no-data">No hay tolvas registradas.</p>
<?php endif; ?>

<script>
function toggleForm() {
    const form = document.getElementById('formulario');
    form.style.display = (form.style.display === 'block') ? 'none' : 'block';
}

function actualizarArchivo(idTolva){
    const sel = document.getElementById("selPdf"+idTolva);
    if(!sel) return;

    const opt = sel.options[sel.selectedIndex];
    const ruta = opt.value;
    const idDoc = opt.getAttribute("data-id");

    const btnDesc = document.getElementById("btnDesc"+idTolva);
    if(btnDesc) btnDesc.href = ruta;

    const delInput = document.getElementById("delDoc"+idTolva);
    if(delInput) delInput.value = idDoc;
}

function verPdf(idTolva){
    const sel = document.getElementById("selPdf"+idTolva);
    if(!sel) return;
    window.open(sel.value, "_blank");
}

document.addEventListener("DOMContentLoaded", function(){
<?php foreach ($tolvas as $t): ?>
    actualizarArchivo(<?= (int)$t['id_tolva'] ?>);
<?php endforeach; ?>
});
</script>

<!-- Modal eliminar tolva -->
<div id="modalEliminarTolva" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:var(--amarillo); padding:22px 26px; border-radius:12px; text-align:center; box-shadow:0 0 18px rgba(0,0,0,.5);">
    <div style="font-weight:900; font-size:18px; color:var(--negro); margin-bottom:16px;">
      ¿Deseas eliminar esta tolva y todos sus archivos?
    </div>
    <button type="button" class="btn btn-eliminar" onclick="confirmarEliminarTolva()">Eliminar</button>
    <button type="button" class="btn btn-volver" onclick="cerrarModalEliminarTolva()">Cancelar</button>
  </div>
</div>

<script>
let formTolvaAEliminar = null;

function abrirModalEliminarTolva(form) {
    formTolvaAEliminar = form;
    document.getElementById('modalEliminarTolva').style.display = 'flex';
}

function cerrarModalEliminarTolva() {
    document.getElementById('modalEliminarTolva').style.display = 'none';
    formTolvaAEliminar = null;
}

function confirmarEliminarTolva() {
    if (formTolvaAEliminar) {
        formTolvaAEliminar.submit();
    }
}
</script>

</body>
</html>


