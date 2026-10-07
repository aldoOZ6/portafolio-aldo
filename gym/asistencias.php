<?php
include 'db_connection.php';

$query = "SELECT asistencias.id_asistencia, alumnos.nombre, asistencias.fecha, asistencias.asistencia 
          FROM asistencias
          INNER JOIN alumnos ON asistencias.id_alumno = alumnos.id_alumno";
$asistencias = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias - Gym Temoaya</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Asistencias de Alumnos</h1>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="alumnos.php">Alumnos</a></li>
                <li><a href="inventario.php">Inventario</a></li>
                <li><a href="ventas.php">Ventas</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Listado de Asistencias</h2>
        <table>
            <tr>
                <th>Alumno</th>
                <th>Fecha</th>
                <th>Asistencia</th>
            </tr>
            <?php foreach ($asistencias as $asistencia): ?>
                <tr>
                    <td><?= $asistencia['nombre'] ?></td>
                    <td><?= $asistencia['fecha'] ?></td>
                    <td><?= $asistencia['asistencia'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Registrar Asistencia</h3>
        <form action="add_asistencia.php" method="POST">
            <label for="id_alumno">Alumno:</label><br>
            <select name="id_alumno" id="id_alumno">
                <?php
                $alumnos_query = "SELECT * FROM alumnos";
                $alumnos_result = $conn->query($alumnos_query);
                foreach ($alumnos_result as $alumno):
                ?>
                    <option value="<?= $alumno['id_alumno'] ?>"><?= $alumno['nombre'] ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="fecha">Fecha:</label><br>
            <input type="date" id="fecha" name="fecha"><br><br>

            <label for="asistencia">Asistencia:</label><br>
            <select name="asistencia" id="asistencia">
                <option value="Presente">Presente</option>
                <option value="Ausente">Ausente</option>
            </select><br><br>

            <button type="submit">Registrar Asistencia</button>
        </form>
        
        <form action="descargar_asistencias.php" method="post" style="margin-bottom: 20px;">
    <button type="submit">📥 Descargar asistencias de hoy</button>
</form>
    </main>

    <footer>
        <p>&copy; 2025 Boxeo Temoaya. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
