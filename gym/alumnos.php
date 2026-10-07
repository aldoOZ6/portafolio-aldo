<?php
include 'db_connection.php';

// Obtener alumnos para mostrar y para select eliminar
$stmt = $conn->query("SELECT * FROM alumnos");
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Alumnos - Gym Temoaya</title>
    <link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<header>
    <h1>Alumnos</h1>
    <nav>
        <ul>
            <li><a href="index.html">Inicio</a></li>
            <li><a href="asistencias.php">Asistencias</a></li>
            <li><a href="inventario.php">Inventario</a></li>
            <li><a href="ventas.php">Ventas</a></li>
        </ul>
    </nav>
</header>

<main>
    <h2>Lista de Alumnos</h2>
    <table>
        <tr>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Municipio</th>
        </tr>
        <?php foreach ($alumnos as $alumno): ?>
            <tr>
                <td><?= htmlspecialchars($alumno['nombre']) ?></td>
                <td><?= htmlspecialchars($alumno['edad']) ?></td>
                <td><?= htmlspecialchars($alumno['municipio']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Agregar Alumno</h3>
    <form action="add_alumno.php" method="POST">
        <label for="nombre">Nombre:</label><br />
        <input type="text" id="nombre" name="nombre" required /><br /><br />
        <label for="edad">Edad:</label><br />
        <input type="number" id="edad" name="edad" required /><br /><br />
        <label for="municipio">Municipio:</label><br />
        <input type="text" id="municipio" name="municipio" required /><br /><br />
        <button type="submit">Agregar Alumno</button>
    </form>

    <!-- Formulario para eliminar alumno -->
    <form class="eliminar-form" action="delete_alumno.php" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este alumno?');" style="text-align: center; margin-top: 30px;">
        <label for="id_alumno">Selecciona alumno a eliminar:</label><br />
        <select name="id_alumno" id="id_alumno" required style="padding: 7px; width: 250px; font-size: 16px;">
            <option value="" disabled selected>Selecciona un alumno</option>
            <?php foreach ($alumnos as $alumno): ?>
                <option value="<?= htmlspecialchars($alumno['id_alumno']) ?>"><?= htmlspecialchars($alumno['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br /><br />
        <button type="submit" style="padding: 10px 20px; background-color: #c0392b; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Eliminar Alumno</button>
    </form>

</main>

<footer>
    <p>&copy; 2025 Boxeo Temoaya. Todos los derechos reservados.</p>
</footer>
</body>
</html>

