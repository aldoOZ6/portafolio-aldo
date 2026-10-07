<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM ventas WHERE id_venta = ?");
    $stmt->execute([$id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        echo "Venta no encontrada.";
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $producto = $_POST['nombre_producto'];
    $cantidad = intval($_POST['cantidad']);
    $precio = floatval($_POST['precio']);
    $fecha = $_POST['fecha'];

    $stmt = $conn->prepare("UPDATE ventas SET nombre_producto = ?, cantidad = ?, precio = ?, fecha = ? WHERE id_venta = ?");
    $stmt->execute([$producto, $cantidad, $precio, $fecha, $id]);

    echo "<script>
            alert('✅ Venta actualizada correctamente.');
            window.location.href = 'ventas.php';
          </script>";
    exit;
} else {
    $ventas = $conn->query("SELECT * FROM ventas");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Ventas</title>
    <link rel="stylesheet" href="css/Eventas.css">
</head>
<body>
    <div class="container">
        <h1>Editar Venta</h1>

        <?php if (!isset($venta) && isset($ventas)): ?>
            <!-- Selección de venta -->
            <form method="GET" action="">
                <label for="id">Selecciona la venta a editar:</label>
                <select name="id" id="id" required>
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($ventas as $v): ?>
                        <option value="<?= htmlspecialchars($v['id_venta']) ?>">
                            <?= htmlspecialchars($v['nombre_producto']) ?> - <?= htmlspecialchars($v['fecha']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-editar">✏️ Editar</button>
            </form>
             <br><a href="ventas.php">Volver a ventas</a>


        <?php elseif (isset($venta)): ?>
            <!-- Formulario para editar venta -->
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?= htmlspecialchars($venta['id_venta']) ?>">

                <label for="nombre_producto">Producto:</label>
                <select name="nombre_producto" id="nombre_producto" required>
                    <?php
                    $productos = $conn->query("SELECT nombre FROM inventario");
                    foreach ($productos as $producto):
                    ?>
                        <option value="<?= htmlspecialchars($producto['nombre']) ?>" <?= ($producto['nombre'] == $venta['nombre_producto']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($producto['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad">Cantidad:</label>
                <input type="number" name="cantidad" id="cantidad" min="1" value="<?= htmlspecialchars($venta['cantidad']) ?>" required>

                <label for="precio">Precio:</label>
                <input type="number" name="precio" id="precio" step="0.01" value="<?= htmlspecialchars($venta['precio']) ?>" required>

                <label for="fecha">Fecha:</label>
                <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($venta['fecha']) ?>" required>

                <!-- Botón estilizado -->
                <button type="submit" class="btn-guardar">🥊 Guardar Cambios</button>
            </form>

            <!-- Botón de regreso al final -->
            <form action="ventas.php" method="get" style="margin-top: 30px;">
                <button type="submit" class="boton-regresar">🔙 Regresar a Ventas</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>




