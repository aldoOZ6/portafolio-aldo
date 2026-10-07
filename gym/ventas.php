<?php
include 'db_connection.php';

$query = "SELECT * FROM ventas";
$ventas = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Gym Temoaya</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Ventas del Gym</h1>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="alumnos.php">Alumnos</a></li>
                <li><a href="asistencias.php">Asistencias</a></li>
                <li><a href="inventario.php">Inventario</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Historial de Ventas</h2>
        <table>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Fecha</th>
            </tr>
            <?php foreach ($ventas as $venta): ?>
                <tr>
                    <td><?= $venta['nombre_producto'] ?></td>
                    <td><?= $venta['cantidad'] ?></td>
                    <td><?= $venta['precio'] ?></td>
                    <td><?= $venta['fecha'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Registrar Venta</h3>
        <form action="add_venta.php" method="POST">
            <label for="nombre_producto">Producto:</label><br>
            <select name="nombre_producto" id="nombre_producto">
                <?php
                $productos_query = "SELECT * FROM inventario";
                $productos_result = $conn->query($productos_query);
                foreach ($productos_result as $producto):
                ?>
                    <option value="<?= $producto['nombre'] ?>"><?= $producto['nombre'] ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="cantidad">Cantidad:</label><br>
            <input type="number" id="cantidad" name="cantidad" min="1" required><br><br>

            <label for="precio">Precio:</label><br>
            <input type="number" id="precio" name="precio" step="0.01" required><br><br>

            <label for="fecha">Fecha de Venta:</label><br>
            <input type="date" id="fecha" name="fecha" required><br><br>

            <button type="submit">Registrar Venta</button>
        </form>

        <form action="editar_venta.php" method="GET">
    <button type="submit">Editar Venta</button>
</form>

        <form action="descargar_ventas.php" method="post" style="margin-bottom: 20px;">
    <button type="submit">📥 Descargar Ventas</button>
</form>

    </main>

    <footer>
        <p>&copy; 2025 Boxeo Temoaya. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
