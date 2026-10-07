<?php 
include 'db_connection.php';
date_default_timezone_set('America/Mexico_City');
$fecha_actual = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $asistencia = $_POST['asistencia'];
    $fecha = $_POST['fecha'];

    // Validar que la fecha sea estrictamente mayor a hoy
    if ($fecha <= $fecha_actual) {
        echo "<script>
                alert('❌ La fecha debe ser posterior a hoy.');
                window.location.href = 'asistencias.php';
              </script>";
        exit;
    }

    // Obtener el id_alumno a partir del nombre
    $stmt = $conn->prepare("SELECT id_alumno FROM alumnos WHERE nombre = ?");
    $stmt->execute([$nombre]);
    $id_alumno = $stmt->fetchColumn();

    if (!$id_alumno) {
        echo "<script>
                alert('❌ Alumno esta entrenando.');
                window.location.href = 'asistencias.php';
              </script>";
        exit;
    }

    // Verificar si ya existe una asistencia para ese alumno en esa fecha
    $stmt = $conn->prepare("SELECT COUNT(*) FROM asistencias WHERE id_alumno = ? AND fecha = ?");
    $stmt->execute([$id_alumno, $fecha]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        echo "<script>
                alert('⚠️ Este alumno ya tiene asistencia registrada en esa fecha.');
                window.location.href = 'asistencias.php';
              </script>";
        exit;
    }

    // Insertar nueva asistencia
    $stmt = $conn->prepare("INSERT INTO asistencias (id_alumno, fecha, asistencia) VALUES (?, ?, ?)");
    $stmt->execute([$id_alumno, $fecha, $asistencia]);

    echo "<script>
            alert('✅ Asistencia registrada correctamente.');
            window.location.href = 'asistencias.php';
          </script>";
    exit;
}
?>

<!-- Formulario HTML -->
<?php
$fecha_actual = date('Y-m-d');
?>

<form method="POST" action="">
    <label for="fecha">Fecha:</label>
    <input type="date" id="fecha" name="fecha" min="<?php echo $fecha_actual; ?>" required>

    <label for="nombre">Nombre del Alumno:</label>
    <input type="text" id="nombre" name="nombre" required>

    <label for="asistencia">Asistencia:</label>
    <select name="asistencia" id="asistencia" required>
        <option value="Presente">Presente</option>
        <option value="Ausente">Ausente</option>
    </select>

    <button type="submit">Registrar</button>
</form>
