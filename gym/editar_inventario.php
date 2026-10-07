<?php
include 'db_connection.php';

// Si se recibe el id_producto por GET, mostramos el formulario para editar ese producto
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM inventario WHERE id_producto = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        echo "Producto no encontrado.";
        exit;
    }
}

// Si se envía el formulario para actualizar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $tipo = $_POST['tipo'];
    $cantidad = intval($_POST['cantidad']);

    $stmt = $conn->prepare("UPDATE inventario SET nombre = ?, marca = ?, tipo = ?, cantidad = ? WHERE id_producto = ?");
    $stmt->execute([$nombre, $marca, $tipo, $cantidad, $id_producto]);

    echo "<script>
            alert('Producto actualizado correctamente.');
            window.location.href = 'inventario.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - Inventario</title>
    <!-- Enlace al archivo CSS externo -->
    <link rel="stylesheet" href="css/estilos_editar_producto.css">
</head>
<body>
    <h1>Editar Producto</h1>

    <?php if (!isset($_GET['id'])): ?>
        <!-- Mostrar listado de productos para seleccionar -->
        <form action="editar_inventario.php" method="GET">
            <label for="id">Selecciona un producto:</label><br>
            <select name="id" id="id" required>
                <option value="">-- Selecciona --</option>
                <?php
                $productos = $conn->query("SELECT id_producto, nombre FROM inventario");
                foreach ($productos as $prod) {
                    echo "<option value='" . $prod['id_producto'] . "'>" . htmlspecialchars($prod['nombre']) . "</option>";
                }
                ?>
            </select><br><br>
            <button type="submit">Editar</button>
        </form>

    <?php else: ?>
        <!-- Mostrar formulario para editar producto seleccionado -->
        <form action="editar_inventario.php" method="POST">
            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">

            <label for="nombre">Nombre del Producto:</label><br>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br><br>

            <label for="marca">Marca:</label><br>
            <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($producto['marca']) ?>" required><br><br>

            <label for="tipo">Tipo de Producto:</label><br>
            <select name="tipo" id="tipo" required>
                <?php
                $tipos = ['Protección', 'Accesorio', 'Suplemento', 'Hidratación', 'Ropa', 'Calzado', 'Higiene'];
                foreach ($tipos as $tipo_op) {
                    $selected = ($tipo_op === $producto['tipo']) ? "selected" : "";
                    echo "<option value='$tipo_op' $selected>$tipo_op</option>";
                }
                ?>
            </select><br><br>

            <label for="cantidad">Cantidad en Inventario:</label><br>
            <input type="number" id="cantidad" name="cantidad" min="0" value="<?= intval($producto['cantidad']) ?>" required><br><br>

            <button type="submit">Guardar Cambios</button>
        </form>
    <?php endif; ?>
    

    <br><a href="inventario.php">Volver al Inventario</a>
</body>
</html>
