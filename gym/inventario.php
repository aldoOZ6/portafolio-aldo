<?php
include 'db_connection.php';

$query = "SELECT * FROM inventario";
$inventario = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Gym Temoaya</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Inventario del Gym</h1>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="alumnos.php">Alumnos</a></li>
                <li><a href="asistencias.php">Asistencias</a></li>
                <li><a href="ventas.php">Ventas</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Listado de Productos en Inventario</h2>
        <table>
            <tr>
                <th>Producto</th>
                <th>Marca</th>
                <th>Tipo</th>
                <th>Cantidad</th>
            </tr>
            <?php foreach ($inventario as $producto): ?>
                <tr>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td><?= htmlspecialchars($producto['marca']) ?></td>
                    <td><?= htmlspecialchars($producto['tipo']) ?></td>
                    <td><?= htmlspecialchars($producto['cantidad']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Agregar Producto al Inventario</h3>
        <form action="add_inventario.php" method="POST">
            <label for="nombre">Nombre del Producto:</label><br>
            <input type="text" id="nombre" name="nombre" required><br><br>

            <label for="marca">Marca:</label><br>
            <input type="text" id="marca" name="marca" required><br><br>

            <label for="tipo">Tipo de Producto:</label><br>
            <select name="tipo" id="tipo" required>
                <option value="">-- Selecciona --</option>
                <option value="Protección">Protección</option>
                <option value="Accesorio">Accesorio</option>
                <option value="Suplemento">Suplemento</option>
                <option value="Hidratación">Hidratación</option>
                <option value="Ropa">Ropa</option>
                <option value="Calzado">Calzado</option>
                <option value="Higiene">Higiene</option>
            </select><br><br>

            <label for="cantidad">Cantidad en Inventario:</label><br>
            <input type="number" id="cantidad" name="cantidad" min="0" required><br><br>

            <button type="submit">Agregar Producto</button>
        </form>

        <br>

        <!-- Botón para ir a la página de edición -->
        <form action="editar_inventario.php" method="GET">
            <button type="submit">Editar Producto</button>
        </form>
        
        <form action="descargar_inventario.php" method="post" style="margin-top: 20px;">
            <button type="submit">📥 Descargar Inventario</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Boxeo Temoaya. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
