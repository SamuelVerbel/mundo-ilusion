<div class="container py-4"><h1 class="section-title">Promociones vigentes</h1>
  <?php if (!$promos): ?><p class="text-muted mt-4">Por ahora no hay promociones vigentes. ¡Vuelve pronto!</p><?php endif; ?>
  <?php foreach ($promos as $pr): ?>
    <div class="promo-banner my-4"><div><span class="pill pill-light">🔥 <?= (int) $pr['porcentaje_descuento'] ?>% de descuento</span><h3 class="mt-2 mb-0"><?= e($pr['nombre']) ?></h3></div>
      <span class="opacity-75">Vigente del <?= fecha($pr['fecha_inicio']) ?> al <?= fecha($pr['fecha_fin']) ?></span></div>
    <div class="row g-4"><?php foreach ($pr['productos'] as $p) require ROOT_PATH . '/app/views/partials/producto_card.php'; ?></div>
  <?php endforeach; ?></div>
