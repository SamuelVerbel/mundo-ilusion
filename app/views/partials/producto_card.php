<?php /** @var array $p */ $href = url('producto/' . (int) $p['id_producto']); $res = resumen_menu($p['descripcion'] ?? ''); ?>
<div class="col-6 col-lg-3">
  <div class="card producto-card h-100">
    <a href="<?= $href ?>" class="producto-img">
      <img src="<?= img_producto($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
      <?php if ($p['con_descuento']): ?><span class="tag tag-promo">-<?= (int) $p['descuento'] ?>%</span><?php endif; ?>
      <?php if (!(int) $p['disponible']): ?><span class="tag tag-agotado">No disponible</span><?php endif; ?>
    </a>
    <div class="card-body d-flex flex-column">
      <small class="text-mi-sec fw-bold text-uppercase"><?= e($p['categoria'] ?? '') ?></small>
      <h6 class="mt-1 mb-1"><a href="<?= $href ?>" class="text-reset text-decoration-none"><?= e($p['nombre']) ?></a></h6>
      <?php if ($res): ?><p class="small text-muted mb-2 card-resumen"><?= e($res) ?>…</p><?php endif; ?>
      <div class="mb-3 mt-auto"><span class="precio"><?= cop($p['precio_final']) ?></span>
        <?php if ($p['con_descuento']): ?><small class="text-muted text-decoration-line-through ms-1"><?= cop($p['precio']) ?></small><?php endif; ?></div>
      <div class="d-grid gap-2">
        <a class="btn btn-sm btn-outline-mi" href="<?= $href ?>">Ver detalle</a>
        <form method="post" action="<?= url('carrito/agregar') ?>"><?= csrf_field() ?><input type="hidden" name="id_producto" value="<?= (int) $p['id_producto'] ?>">
          <button class="btn btn-sm btn-mi w-100" <?= (int) $p['disponible'] ? '' : 'disabled' ?>><i class="bi bi-cart-plus"></i> Agregar</button></form>
      </div>
    </div>
  </div>
</div>
