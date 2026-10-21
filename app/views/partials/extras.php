<?php /** @var array $extras  @var string $titulo */ if (!empty($extras)): ?>
<div class="extras-box">
  <h6 class="mb-1"><i class="bi bi-stars text-mi"></i> <?= e($titulo ?? 'Agrega un detalle extra') ?></h6>
  <p class="small text-muted mb-2">Complementa tu pedido con estos adicionales.</p>
  <div class="row g-2"><?php foreach ($extras as $x): ?>
    <div class="col-6 col-md-4"><form method="post" action="<?= url('carrito/agregar') ?>" class="extra-chip"><?= csrf_field() ?><input type="hidden" name="id_producto" value="<?= (int) $x['id_producto'] ?>">
      <img src="<?= img_producto($x['imagen']) ?>" alt=""><div class="flex-grow-1"><div class="small fw-bold lh-sm"><?= e($x['nombre']) ?></div><div class="small text-mi fw-bold">+<?= cop($x['precio_final']) ?></div></div>
      <button class="btn btn-sm btn-outline-mi px-2" title="Agregar"><i class="bi bi-plus-lg"></i></button></form></div>
  <?php endforeach; ?></div>
</div>
<?php endif; ?>
