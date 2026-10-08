<?php include __DIR__ . '/../../Views/layouts/main.php'; ?>
<?php if (empty($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) { header('Location: index.php'); exit; } ?>
<div class="row">
  <div class="col-md-10 offset-md-1">
    <h3>Panel de administración - Productos</h3>
    <a class="btn btn-success mb-3" href="index.php?page=admin_create_product">Nuevo producto</a>
    <table class="table">
      <thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr></thead>
      <tbody>
        <?php foreach($products as $p): ?>
          <tr>
            <td><?=htmlspecialchars($p['id'])?></td>
            <td><?=htmlspecialchars($p['name'])?></td>
            <td><?=htmlspecialchars($p['category_name'] ?? '')?></td>
            <td>$<?=number_format($p['price'],2)?></td>
            <td><?=intval($p['stock'] ?? 0)?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
