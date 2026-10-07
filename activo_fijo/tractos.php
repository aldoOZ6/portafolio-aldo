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

    // Registrar tracto
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
        $sqlInsert = "INSERT INTO tractos (inventario, descripcion, modelo, num_serie, marca, placas_vehiculo, fecha_adquisicion, factura, poliza_seguro)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            $_POST['inventario'],
            $_POST['descripcion'],
            $_POST['modelo'],
            $_POST['num_serie'],
            $_POST['marca'],
            $_POST['placas_vehiculo'],
            $_POST['fecha_adquisicion'],
            $_POST['factura'],
            $_POST['poliza_seguro'],
        ]);

        header("Location: tractos.php?msg=registrado");
        exit;
    }

    // Obtener tractos
    $sql = "SELECT * FROM tractos ORDER BY id_tracto DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tractos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener documentos
    $sqlDoc = "SELECT * FROM documentos_tractos ORDER BY id_documento DESC";
    $stmtDoc = $pdo->prepare($sqlDoc);
    $stmtDoc->execute();
    $documentos = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

    $documentosPorTracto = [];
    foreach ($documentos as $doc) {
        $documentosPorTracto[$doc['id_tracto']][] = $doc;
    }

} catch (PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}

// Mensajes
$mensaje = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'registrado') $mensaje = "Tracto registrado correctamente.";
    if ($_GET['msg'] === 'eliminado') $mensaje = "Tracto eliminado correctamente.";
    if ($_GET['msg'] === 'archivo_eliminado') $mensaje = "Archivo eliminado correctamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tractos Registrados</title>

    <style>
        :root{
            --amarillo: #fbff00ff;
            --amarillo-claro: #fffacd;
            --negro:#000;
            --azul:#007bff;
            --azul-oscuro:#0056b3;
            --rojo:#dc3545;
            --rojo-oscuro:#a71d2a;
        }

        body{
            font-family: Arial, sans-serif;
            background: url('img/moderna1.jpg') no-repeat center center fixed;
            background-size: cover;
            margin:0;
            padding: 30px 20px;
            color: var(--negro);
        }

        h2{
            text-align:center;
            font-weight:900;
            font-size: 48px;
            color: var(--negro);
            margin: 0 0 10px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.35);
        }

        /* Mensaje debajo del título */
        .msg{
            max-width: 900px;
            margin: 12px auto 18px auto;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 900;
            text-align:center;
            background: #d4edda;
            color:#155724;
            border: 1px solid #c3e6cb;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
        }

        /* Botones de arriba */
        .top-btns{
            text-align:center;
            margin-bottom: 18px;
        }

        .btn{
            padding: 12px 18px;
            border-radius: 10px;
            border: none;
            font-weight: 900;
            font-size: 15px;
            cursor: pointer;
            margin: 5px 8px;
            user-select:none;
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
            text-decoration:none;
            display:inline-block;
        }

        .btn-registrar{
            background: var(--azul);
            color: white;
        }
        .btn-registrar:hover{ background: var(--azul-oscuro); }

        .btn-volver{
            background: #6c757d;
            color: white;
        }
        .btn-volver:hover{ background: #565e64; }

        .btn-eliminar{
            background: var(--rojo);
            color: white;
        }
        .btn-eliminar:hover{ background: var(--rojo-oscuro); }

        /* Caja amarilla subir archivo (NO QUITAR, como tu imagen) */
        .form-subir{
            max-width: 1050px;
            margin: 0 auto 22px auto;
            background: var(--amarillo);
            padding: 22px 26px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.45);
            border: 2px solid rgba(0,0,0,0.25);
        }

        .form-subir label{
            font-weight: 900;
            font-size: 20px;
            display:block;
            margin-bottom: 12px;
        }

        .form-subir select{
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 2px solid rgba(0,0,0,0.45);
            font-weight: 900;
            background: #f2f2f2;
            margin-bottom: 14px;
        }

        .file-box{
            width: 100%;
            border: 2px solid rgba(0,0,0,0.45);
            border-radius: 10px;
            padding: 12px;
            background: var(--amarillo);
            margin-bottom: 18px;
        }

        .form-subir input[type="file"]{
            width: 100%;
        }

        .btn-subir{
            background: var(--azul);
            color:white;
            padding: 12px 18px;
            border-radius: 10px;
            border:none;
            font-weight: 900;
            cursor:pointer;
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
        }
        .btn-subir:hover{ background: var(--azul-oscuro); }

        /* Tabla */
        table{
            width:100%;
            max-width: 1100px;
            margin: 20px auto 0 auto;
            border-collapse: collapse;
            background: var(--amarillo);
            border-radius: 12px;
            overflow:hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.45);
            color: var(--negro);
            font-weight: 900;
        }

        th, td{
            padding: 12px 10px;
            border: 1px solid rgba(0,0,0,0.45);
            text-align:center;
            font-size: 15px;
            vertical-align: middle;
        }

        th{
            background: var(--azul);
            color:white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:nth-child(even){ background: var(--amarillo-claro); }

        /* Archivos dentro de tabla como tu ejemplo */
        .archivos-cell{
            background: var(--amarillo);
        }

        .archivos-cell .estado{
            font-weight: 900;
            margin-bottom: 8px;
            display:block;
        }

        .archivos-box{
            background: var(--amarillo-claro);
            padding: 10px;
            border-radius: 10px;
            border: 2px solid rgba(0,0,0,0.25);
        }

        .archivos-box select{
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 2px solid rgba(0,0,0,0.35);
            font-weight: 900;
            background: #f2f2f2;
        }

        /* ✅ SOLO CAMBIO: BOTONES COMO TU IMAGEN */
        .archivos-acciones{
            margin-top: 10px;
            display:none;
            justify-content:center;
            align-items:center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .btn-mini{
            border-radius: 10px;
            padding: 6px 0;
            font-weight: 900;
            font-size: 13px;
            text-decoration:none;
            cursor:pointer;
            display:inline-block;
            width: 95px;
            text-align:center;
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
        }

        .btn-ver, .btn-descargar{
            background: var(--azul);
            color: white;
        }
        .btn-ver:hover, .btn-descargar:hover{
            background: var(--azul-oscuro);
        }

        .btn-eliminar-archivo{
            background: var(--rojo);
            color: white;
        }
        .btn-eliminar-archivo:hover{
            background: var(--rojo-oscuro);
        }

        /* Formulario registrar */
        .form-container{
            max-width: 700px;
            margin: 0 auto 18px auto;
            background: var(--amarillo);
            padding: 22px 26px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.45);
            border: 2px solid rgba(0,0,0,0.25);
            display:none;
            font-weight: 900;
        }

        .form-container label{
            display:block;
            margin-top: 12px;
            font-weight:900;
        }

        .form-container input{
            width:100%;
            padding: 10px 14px;
            margin-top: 6px;
            border-radius: 10px;
            border: 2px solid rgba(0,0,0,0.35);
            background: var(--amarillo-claro);
            font-weight: 900;
        }

        .form-container button{
            width:100%;
            margin-top: 18px;
            padding: 14px 0;
            background: var(--azul);
            color:white;
            border:none;
            border-radius: 12px;
            font-weight: 900;
            font-size: 18px;
            cursor:pointer;
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
        }

        .form-container button:hover{ background: var(--azul-oscuro); }
    </style>
</head>

<body>

<h2>Tractos Registrados</h2>

<?php if ($mensaje): ?>
    <div class="msg"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="top-btns">
    <button class="btn btn-registrar" onclick="toggleForm()">Registrar Tracto</button>
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

        <label>Marca</label>
        <input type="text" name="marca">

        <label>Placas del Vehículo</label>
        <input type="text" name="placas_vehiculo">

        <label>Fecha de Adquisición</label>
        <input type="date" name="fecha_adquisicion">

        <label>Factura</label>
        <input type="text" name="factura">

        <label>Póliza de Seguro</label>
        <input type="text" name="poliza_seguro">

        <button type="submit">Registrar</button>
    </form>
</div>

<!-- ✅ ESTA ES LA PARTE QUE ME DIJISTE QUE NO QUITARA -->
<div class="form-subir">
    <form action="subir_archivo_tracto.php" method="POST" enctype="multipart/form-data">
        <label for="id_tracto">Seleccionar Tracto:</label>

        <select name="id_tracto" required>
            <option value="">-- Selecciona un tracto --</option>
            <?php foreach ($tractos as $t): ?>
                <option value="<?= $t['id_tracto'] ?>">Tracto <?= htmlspecialchars($t['inventario']) ?> - <?= htmlspecialchars($t['descripcion']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="file-box">
            <input type="file" name="archivo" accept=".pdf" required />
        </div>

        <button type="submit" class="btn-subir">Subir Archivo</button>
    </form>
</div>
<!-- ✅ FIN DE LA PARTE QUE NO SE DEBE QUITAR -->

<?php if (count($tractos) > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Inventario</th>
            <th>Descripción</th>
            <th>Modelo</th>
            <th>Serie</th>
            <th>Marca</th>
            <th>Placas</th>
            <th>Fecha Adq.</th>
            <th>Factura</th>
            <th>Póliza</th>
            <th>Archivos</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tractos as $t): ?>
            <tr>
                <td><?= $t['id_tracto'] ?></td>
                <td><?= htmlspecialchars($t['inventario']) ?></td>
                <td><?= htmlspecialchars($t['descripcion']) ?></td>
                <td><?= htmlspecialchars($t['modelo']) ?></td>
                <td><?= htmlspecialchars($t['num_serie']) ?></td>
                <td><?= htmlspecialchars($t['marca']) ?></td>
                <td><?= htmlspecialchars($t['placas_vehiculo']) ?></td>
                <td><?= htmlspecialchars($t['fecha_adquisicion']) ?></td>
                <td><?= htmlspecialchars($t['factura']) ?></td>
                <td><?= htmlspecialchars($t['poliza_seguro']) ?></td>

                <!-- ✅ ARCHIVOS: VER / DESCARGAR / ELIMINAR (sin confirm) -->
                <td class="archivos-cell">
                    <?php if (!empty($documentosPorTracto[$t['id_tracto']])): ?>
                        <span class="estado">Con archivos</span>

                        <div class="archivos-box">
                            <select onchange="mostrarBotones(this)">
                                <option value="">REQUISITOS PARA ANTEPROYECTO E INFORME FINAL</option>
                                <?php foreach ($documentosPorTracto[$t['id_tracto']] as $doc): ?>
                                    <option
                                        value="<?= htmlspecialchars($doc['ruta_archivo']) ?>"
                                        data-id="<?= $doc['id_documento'] ?>">
                                        <?= htmlspecialchars($doc['nombre_archivo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="archivos-acciones">
                                <a class="btn-mini btn-ver" target="_blank">Ver</a>
                                <a class="btn-mini btn-descargar" download>Descargar</a>
                                <a class="btn-mini btn-eliminar-archivo">Eliminar</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="estado">Sin archivos</span>
                    <?php endif; ?>
                </td>

                <!-- Eliminar tracto -->
                <td>
                    <a class="btn btn-eliminar"
                       href="eliminar_tracto.php?id=<?= $t['id_tracto'] ?>"
                       onclick="return confirm('¿Eliminar tracto?');">
                        Eliminar
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p style="text-align:center; color:red; font-weight:900;">No hay tractos registrados.</p>
<?php endif; ?>

<script>
function toggleForm() {
    const form = document.getElementById('formulario');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

function mostrarBotones(select) {
    const box = select.closest('.archivos-box');
    const acciones = box.querySelector('.archivos-acciones');

    const btnVer = acciones.querySelector('.btn-ver');
    const btnDescargar = acciones.querySelector('.btn-descargar');
    const btnEliminar = acciones.querySelector('.btn-eliminar-archivo');

    const opt = select.options[select.selectedIndex];
    const ruta = opt.value;
    const id = opt.getAttribute('data-id');

    if (ruta && id) {
        btnVer.href = ruta;
        btnDescargar.href = ruta;

        // ✅ Eliminar SIN confirm() (para quitar el mensaje)
        btnEliminar.href = "eliminar_archivo_tracto.php?id=" + id;
        btnEliminar.onclick = null;

        acciones.style.display = "flex";
    } else {
        acciones.style.display = "none";
        btnVer.href = "#";
        btnDescargar.href = "#";
        btnEliminar.href = "#";
        btnEliminar.onclick = null;
    }
}
</script>

</body>
</html>
