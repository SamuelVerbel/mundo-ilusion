<div class="container py-4"><h1 class="section-title">Mi carrito</h1>
<?php if (!$items): ?><div class="text-center py-5"><div style="font-size:3.5rem">🛒</div><p class="text-muted">Tu carrito está vacío.</p><a href="<?= url('catalogo') ?>" class="btn btn-mi">Explorar el catálogo</a></div>
<?php else: ?>
<div class="row g-4 mt-1"><div class="col-lg-8">
  <?php foreach ($items as $i): ?>
  <div class="card mb-3 p-3"><div class="d-flex gap-3 align-items-center flex-wrap">
    <img src="<?= img_producto($i['imagen']) ?>" class="cart-img" alt="">
    <div class="flex-grow-1"><a class="fw-bold text-reset text-decoration-none" href="<?= url('producto/' . (int) $i['id_producto']) ?>"><?= e($i['nombre']) ?></a>
      <div class="small"><span class="precio"><?= cop($i['precio_final']) ?></span><?php if ($i['descuento'] > 0): ?> <small class="text-muted text-decoration-line-through"><?= cop($i['precio']) ?></small> <span class="badge bg-mi">-<?= (int) $i['descuento'] ?>%</span><?php endif; ?></div>
      <?php if (!$i['comprable']): ?><div class="small text-danger"><i class="bi bi-exclamation-triangle"></i> Ya no está disponible. Elimínalo para continuar.</div><?php endif; ?></div>
    <form method="post" action="<?= url('carrito/actualizar') ?>" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="id_producto" value="<?= (int) $i['id_producto'] ?>">
      <input type="number" name="cantidad" min="1" max="99" value="<?= (int) $i['cantidad'] ?>" class="form-control form-control-sm" style="width:76px"><button class="btn btn-sm btn-outline-mi" title="Actualizar"><i class="bi bi-arrow-repeat"></i></button></form>
    <div class="fw-bold" style="min-width:90px;text-align:right"><?= cop($i['subtotal']) ?></div>
    <form method="post" action="<?= url('carrito/eliminar') ?>"><?= csrf_field() ?><input type="hidden" name="id_producto" value="<?= (int) $i['id_producto'] ?>"><button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button></form>
  </div></div><?php endforeach; ?></div>
  <div class="col-lg-4"><div class="card p-4"><h5>Resumen</h5>
    <div class="d-flex justify-content-between mt-2"><span>Subtotal</span><span><?= cop($r['subtotal']) ?></span></div>
    <?php if ($r['descuento'] > 0): ?><div class="d-flex justify-content-between text-success"><span>Descuentos</span><span>-<?= cop($r['descuento']) ?></span></div><?php endif; ?>
    <hr><div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span class="precio"><?= cop($r['total']) ?></span></div>
    <a href="<?= url('checkout') ?>" class="btn btn-mi btn-lg mt-3 <?= $r['no_disponibles'] ? 'disabled' : '' ?>">Continuar con el pedido</a>
    <a href="<?= url('catalogo') ?>" class="btn btn-link link-mi mt-1">Seguir comprando</a></div></div></div>
<?php endif; ?><?php if (!empty($extras)): ?><div class="mt-4"><?php $titulo = '¿Quieres agregar algo más?'; require ROOT_PATH . '/app/views/partials/extras.php'; ?></div><?php endif; ?></div>
