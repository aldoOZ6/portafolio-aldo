<?php
// Conexión PDO con MySQL en puerto 3307, usuario root sin contraseña
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "activo_fijo";
$puerto = 3307;

$msg = ""; // <-- AGREGADO

// <-- AGREGADO: leer mensaje por URL (registrar/eliminar)
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'registrado') $msg = "Caja seca registrada correctamente.";
    if ($_GET['msg'] === 'eliminado')  $msg = "Caja seca eliminada correctamente.";
}

try {
    $dsn = "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8";
    $pdo = new PDO($dsn, $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Registrar unidad
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
        $sqlInsert = "INSERT INTO unidades (inventario, descripcion, modelo, num_serie, fecha_adquisicion, placas_vehiculo, factura, tjc, poliza_seguro, inciso)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            $_POST['inventario'],
            $_POST['descripcion'],
            $_POST['modelo'],
            $_POST['num_serie'],
            $_POST['fecha_adquisicion'],
            $_POST['placas_vehiculo'],
            $_POST['factura'],
            $_POST['tjc'],
            $_POST['poliza_seguro'],
            $_POST['inciso'],
        ]);

        header("Location: unidades.php?msg=registrado");
        exit;
    }

    // Obtener unidades
    $sql = "SELECT * FROM unidades ORDER BY id_unidad DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener documentos
    $sqlDoc = "SELECT * FROM documentos ORDER BY id_documento DESC";
    $stmtDoc = $pdo->prepare($sqlDoc);
    $stmtDoc->execute();
    $documentos = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

    $documentosPorUnidad = [];
    foreach ($documentos as $doc) {
        $documentosPorUnidad[$doc['id_unidad']][] = $doc;
    }

} catch (PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Caja Secas Registradas</title>
    <style>
    :root{
        --amarillo: #fbff00ff;
        --amarillo-claro: #fffacd;
        --azul: #007bff;
        --azul-oscuro: #0056b3;
        --negro:#000;
        --rojo:#dc3545;
        --rojo-oscuro:#a71d2a;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 30px 20px;
        background: url('img/moderna1.jpg') no-repeat center center fixed;
        background-size: cover;
        color: var(--negro);
    }

    h2 {
        text-align: center;
        font-weight: 900;
        font-size: 42px;
        color: var(--negro);
        margin: 10px 0 10px 0;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.35);
    }

    /* estilos del mensaje */
    .msg{
        max-width: 900px;
        margin: 12px auto 18px auto;
        padding: 14px 16px;
        border-radius: 12px;
        font-weight: 900;
        text-align: center;
        box-shadow: 0 6px 18px rgba(0,0,0,0.25);
        border: 2px solid rgba(0,0,0,0.15);
        background: #d4edda;
        color: #155724;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1100px;
        margin: 0 auto 20px auto;
    }

    /* Botones */
    .btn-volver, .btn-archivo, .btn-eliminar, .btn-registrar {
        display: inline-block;
        padding: 12px 18px;
        margin: 5px;
        background-color: var(--azul);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 900;
        cursor: pointer;
        user-select: none;
        border: none;
        box-shadow: 0 6px 18px rgba(0,0,0,0.35);
        transition: background-color .2s ease, transform .05s ease;
    }

    .btn-volver:hover, .btn-archivo:hover, .btn-registrar:hover {
        background-color: var(--azul-oscuro);
    }

    .btn-volver:active, .btn-archivo:active, .btn-registrar:active,
    .btn-eliminar:active {
        transform: scale(0.99);
    }

    .btn-eliminar {
        background-color: var(--rojo);
    }
    .btn-eliminar:hover {
        background-color: var(--rojo-oscuro);
    }

    .form-subir {
        max-width: 900px;
        margin: 0 auto 20px auto;
        background: var(--amarillo);
        padding: 18px 20px;
        border-radius: 16px;
        box-shadow: 0 0 20px rgba(0,0,0,0.45);
        border: 2px solid rgba(0,0,0,0.25);
        text-align: center;
        font-weight: 900;
    }

    .form-subir label{
        font-weight: 900;
        margin-right: 10px;
    }

    .form-subir select{
        padding: 10px 12px;
        border-radius: 10px;
        border: 2px solid rgba(0,0,0,0.45);
        font-weight: 900;
        background: var(--amarillo-claro);
        color: var(--negro);
        margin: 0 10px;
        max-width: 360px;
    }

    .form-subir input[type="file"]{
        padding: 10px;
        border-radius: 10px;
        border: 2px solid rgba(0,0,0,0.45);
        background: var(--amarillo-claro);
        font-weight: 900;
        margin: 0 10px;
    }

    /* Form registrar */
    form#formRegistrar {
        max-width: 700px;
        margin: 0 auto 25px auto;
        background: var(--amarillo);
        padding: 22px 26px;
        border-radius: 16px;
        box-shadow: 0 0 20px rgba(0,0,0,0.45);
        border: 2px solid rgba(0,0,0,0.25);
        display: none;
        color: var(--negro);
        font-weight: 900;
    }

    form#formRegistrar label {
        display: block;
        margin-top: 12px;
        font-weight: 900;
        text-align: left;
    }

    form#formRegistrar input {
        width: 100%;
        padding: 10px 14px;
        margin-top: 6px;
        border-radius: 10px;
        border: 2px solid rgba(0,0,0,0.45);
        background: var(--amarillo-claro);
        font-weight: 900;
        color: var(--negro);
        outline: none;
    }

    form#formRegistrar input:focus{
        border-color: var(--azul);
        box-shadow: 0 0 10px rgba(0,123,255,0.35);
    }

    form#formRegistrar button {
        margin-top: 18px;
        padding: 14px 0;
        width: 100%;
        background: var(--azul);
        color: white;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 900;
        box-shadow: 0 6px 18px rgba(0,0,0,0.35);
        transition: background-color .2s ease;
    }

    form#formRegistrar button:hover {
        background: var(--azul-oscuro);
    }

    table {
        width: 100%;
        max-width: 1100px;
        margin: 20px auto 0 auto;
        border-collapse: collapse;
        background: var(--amarillo);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.45);
        color: var(--negro);
        font-weight: 900;
    }

    th, td {
        padding: 12px 10px;
        border: 1px solid rgba(0,0,0,0.45);
        text-align: center;
        font-size: 15px;
        vertical-align: middle;
    }

    th {
        background-color: var(--azul);
        color: white;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    tr:nth-child(even) {
        background-color: var(--amarillo-claro);
    }

    td select{
        padding: 8px 10px;
        border-radius: 10px;
        border: 2px solid rgba(0,0,0,0.45);
        background: var(--amarillo-claro);
        font-weight: 900;
    }

    /* ✅ SOLO AJUSTADO: BOTONES EN ORDEN Y EN FILA (COMO TU IMAGEN) */
    .botones-archivo{
        display:none;
        margin-top:6px;
        display:flex;
        gap:8px;
        justify-content:center;
        align-items:center;
        flex-wrap:nowrap; /* una sola fila */
    }
    .btn-mini{
        display:inline-block;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 900;
        border-radius: 8px;
        text-decoration:none;
        border:none;
        cursor:pointer;
        user-select:none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        white-space:nowrap;
    }
    .btn-mini-azul{
        background: var(--azul);
        color:#fff;
    }
    .btn-mini-azul:hover{
        background: var(--azul-oscuro);
    }
    .btn-mini-rojo{
        background: var(--rojo);
        color:#fff;
    }
    .btn-mini-rojo:hover{
        background: var(--rojo-oscuro);
    }
    </style>
</head>
<body>

<h2>Caja Secas Registradas</h2>

<?php if (!empty($msg)): ?>
    <div class="msg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="top-bar">
    <button id="btnMostrarForm" class="btn-registrar">Registrar Nueva Unidad</button>
    <a href="index.php" class="btn-volver">Volver al Menú Principal</a>
</div>

<form id="formRegistrar" method="POST" action="unidades.php">
    <input type="hidden" name="accion" value="registrar" />

    <label for="inventario">Número de Inventario</label>
    <input type="text" id="inventario" name="inventario" required />

    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" required />

    <label for="modelo">Modelo (Año)</label>
    <input type="number" id="modelo" name="modelo" min="1900" max="2100" />

    <label for="num_serie">Número de Serie</label>
    <input type="text" id="num_serie" name="num_serie" required />

    <label for="fecha_adquisicion">Fecha de Adquisición</label>
    <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" />

    <label for="placas_vehiculo">Número de Placa del Vehículo</label>
    <input type="text" id="placas_vehiculo" name="placas_vehiculo" />

    <label for="factura">Factura (FAC)</label>
    <input type="text" id="factura" name="factura" />

    <label for="tjc">TJC</label>
    <input type="text" id="tjc" name="tjc" />

    <label for="poliza_seguro">Póliza de Seguro</label>
    <input type="text" id="poliza_seguro" name="poliza_seguro" />

    <label for="inciso">Inciso</label>
    <input type="text" id="inciso" name="inciso" />

    <button type="submit">Registrar Unidad</button>
</form>

<div class="form-subir">
    <form action="subir_archivo.php" method="POST" enctype="multipart/form-data">
        <label for="id_unidad">Seleccionar Unidad:</label>
        <select name="id_unidad" required>
            <option value="">-- Selecciona una unidad --</option>
            <?php foreach ($unidades as $unidad): ?>
                <option value="<?= $unidad['id_unidad'] ?>">
                    Unidad <?= htmlspecialchars($unidad['inventario']) ?> - <?= htmlspecialchars($unidad['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="file" name="archivo" accept=".pdf" required />
        <button type="submit" class="btn-archivo">Subir Archivo</button>
    </form>
</div>

<?php if (count($unidades) > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Número de Inventario</th>
            <th>Descripción</th>
            <th>Modelo</th>
            <th>Serie</th>
            <th>Fecha Adquisición</th>
            <th>Placas</th>
            <th>Factura</th>
            <th>TJC</th>
            <th>Póliza</th>
            <th>Inciso</th>
            <th>Archivos</th>
            <th>Eliminar Unidad</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($unidades as $unidad): ?>
            <tr>
                <td><?= $unidad['id_unidad'] ?></td>
                <td><?= htmlspecialchars($unidad['inventario']) ?></td>
                <td><?= htmlspecialchars($unidad['descripcion']) ?></td>
                <td><?= htmlspecialchars($unidad['modelo']) ?></td>
                <td><?= htmlspecialchars($unidad['num_serie']) ?></td>
                <td><?= htmlspecialchars($unidad['fecha_adquisicion']) ?></td>
                <td><?= htmlspecialchars($unidad['placas_vehiculo']) ?></td>
                <td><?= htmlspecialchars($unidad['factura']) ?></td>
                <td><?= htmlspecialchars($unidad['tjc']) ?></td>
                <td><?= htmlspecialchars($unidad['poliza_seguro']) ?></td>
                <td><?= htmlspecialchars($unidad['inciso']) ?></td>

                <td>
                    <?php if (!empty($documentosPorUnidad[$unidad['id_unidad']])): ?>
                        <select onchange="mostrarBotones(this)">
                            <option value="">-- Seleccionar archivo --</option>
                            <?php foreach ($documentosPorUnidad[$unidad['id_unidad']] as $doc): ?>
                                <option value="<?= htmlspecialchars($doc['ruta_archivo']) ?>" data-id="<?= $doc['id_documento'] ?>">
                                    <?= htmlspecialchars($doc['nombre_archivo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- ✅ BOTONES EN ORDEN: VER / DESCARGAR / ELIMINAR (SIN MENSAJE) -->
                        <div class="botones-archivo">
                            <a class="btn-mini btn-mini-azul btn-ver" target="_blank">Ver</a>
                            <a class="btn-mini btn-mini-azul btn-descargar" download>Descargar</a>

                            <!-- ✅ AQUÍ QUITÉ el confirm() -->
                            <a class="btn-mini btn-mini-rojo btn-eliminar-archivo">Eliminar</a>
                        </div>

                    <?php else: ?>
                        Sin archivos
                    <?php endif; ?>
                </td>

                <td>
                    <a class="btn-eliminar" href="eliminar_unidad.php?id=<?= $unidad['id_unidad'] ?>"
                       onclick="return confirm('¿Eliminar esta unidad?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p style="text-align:center; color:red;">No hay unidades registradas.</p>
<?php endif; ?>

<script>
document.getElementById('btnMostrarForm').addEventListener('click', function() {
    const form = document.getElementById('formRegistrar');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        this.textContent = 'Ocultar Formulario';
    } else {
        form.style.display = 'none';
        this.textContent = 'Registrar Nueva Unidad';
    }
});

// ✅ NO TOCA TU LÓGICA: solo setea links en la misma fila
function mostrarBotones(select) {
    const div = select.nextElementSibling; // .botones-archivo
    const selected = select.options[select.selectedIndex];
    const ruta = selected.value;
    const id = selected.getAttribute("data-id");

    if (ruta && id) {
        div.style.display = "flex";
        div.querySelector(".btn-ver").href = ruta;
        div.querySelector(".btn-descargar").href = ruta;
        div.querySelector(".btn-eliminar-archivo").href = "eliminar_archivo.php?id=" + id;
    } else {
        div.style.display = "none";
    }
}
</script>

</body>
</html>

