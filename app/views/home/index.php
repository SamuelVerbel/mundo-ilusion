<?php $wa = whatsapp_url('Hola Mundo Ilusión, quiero cotizar un desayuno temático'); $fotos = array_slice($destacados, 0, 3); ?>
<section class="hero"><div class="container"><div class="row align-items-center g-5">
  <div class="col-lg-6">
    <span class="pill"><i class="bi bi-gift"></i> <?= e(conf('tienda_slogan', 'Tienda de regalos')) ?></span>
    <h1 class="display-4 mt-3">Detalles que convierten momentos en <em>inolvidables</em></h1>
    <p class="lead"><?= e(conf('tienda_mensaje')) ?></p>
    <div class="d-flex gap-3 mt-4 flex-wrap"><a href="<?= url('catalogo') ?>" class="btn btn-mi btn-lg">Ver catálogo</a><a href="#como-pedir" class="btn btn-outline-mi btn-lg">¿Cómo pedir?</a></div>
    <div class="d-flex gap-4 mt-4 small text-muted flex-wrap"><span><i class="bi bi-palette text-mi"></i> 100% personalizado</span><span><i class="bi bi-calendar-heart text-mi"></i> Eliges la fecha</span><span><i class="bi bi-shield-check text-mi"></i> Pago seguro</span></div>
  </div>
  <div class="col-lg-6"><div class="hero-collage">
    <?php foreach ($fotos as $i => $p): ?><a href="<?= url('producto/' . (int) $p['id_producto']) ?>" class="hc hc<?= $i + 1 ?>"><img src="<?= img_producto($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>"></a><?php endforeach; ?>
    <img src="<?= asset('img/logo.png') ?>" alt="" class="hc-logo">
  </div></div>
</div></div></section>

<section class="container py-5">
  <h2 class="section-title text-center">Elige tu categoría</h2>
  <div class="row g-4 mt-1 justify-content-center">
    <?php foreach ($categorias as $c): ?>
      <div class="col-md-4"><a href="<?= url('catalogo?categoria=' . (int) $c['id_categoria']) ?>" class="cat-photo">
        <img src="<?= img_producto($c['imagen']) ?>" alt="<?= e($c['nombre']) ?>" loading="lazy">
        <div class="cat-photo-info"><strong><?= e($c['nombre']) ?></strong><span><?= (int) $c['total'] ?> opciones</span></div></a></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container py-4">
  <div class="d-flex justify-content-between align-items-end mb-3"><h2 class="section-title mb-0">Para sorprender hoy</h2><a href="<?= url('catalogo') ?>" class="link-mi">Ver todo →</a></div>
  <div class="row g-4"><?php foreach ($destacados as $p) require ROOT_PATH . '/app/views/partials/producto_card.php'; ?></div>
</section>

<?php if ($promos): $pr = $promos[0]; ?>
<section class="container py-4"><div class="promo-banner">
  <div><span class="pill pill-light"><i class="bi bi-fire"></i> Promoción vigente</span><h3 class="mt-2"><?= e($pr['nombre']) ?> · <?= (int) $pr['porcentaje_descuento'] ?>% de descuento</h3>
    <p class="mb-0 opacity-75">Hasta el <?= fecha($pr['fecha_fin']) ?> en productos seleccionados.</p></div>
  <a href="<?= url('promociones') ?>" class="btn btn-light btn-lg">Aprovechar</a></div></section>
<?php endif; ?>

<section class="tematicos my-5"><div class="container"><div class="row align-items-center g-4">
  <div class="col-lg-5"><span class="pill pill-light">✨ Hechos a tu medida</span>
    <h2 class="mt-3">Desayunos temáticos</h2>
    <p class="opacity-90">Elige tu personaje, equipo o temática favorita y lo armamos según las especificaciones de la referencia: con arco de globos o con un solo globo, como prefieras.</p>
    <a href="<?= $wa ?: url('pqr') ?>" <?= $wa ? 'target="_blank" rel="noopener"' : '' ?> class="btn btn-light btn-lg"><i class="bi bi-<?= $wa ? 'whatsapp' : 'chat-heart' ?>"></i> Pedir cotización</a></div>
  <div class="col-lg-7"><div class="tem-grid"><?php for ($i = 1; $i <= 6; $i++): ?><img src="<?= asset('img/tematicos/t' . $i . '.png') ?>" alt="Desayuno temático" loading="lazy"><?php endfor; ?></div></div>
</div></div></section>

<section class="container py-4" id="como-pedir">
  <h2 class="section-title text-center">¿Cómo pedir?</h2>
  <div class="row g-4 mt-1 text-center">
    <?php foreach ([['bi-gift', '1. Elige tu detalle', 'Explora el catálogo y agrega al carrito el que más te guste.'], ['bi-palette', '2. Personalízalo', 'Cuéntanos tu temática, colores y dedicatoria al hacer el pedido.'], ['bi-credit-card', '3. Paga en línea', 'Confirma con pago seguro y escoge la fecha de entrega o recogida.']] as [$ic, $t, $d]): ?>
      <div class="col-md-4"><div class="paso"><i class="bi <?= $ic ?>"></i><h5><?= $t ?></h5><p class="text-muted mb-0"><?= $d ?></p></div></div><?php endforeach; ?>
  </div>
</section>

<section class="container py-4"><div class="row align-items-center g-4 sobre">
  <div class="col-md-5"><img src="<?= asset('img/tienda.jpg') ?>" alt="Nuestra tienda" class="img-fluid rounded-4 shadow"></div>
  <div class="col-md-7"><span class="pill">Nuestra tienda</span><h2 class="mt-3">Hecho con amor y propósito</h2>
    <p class="lead text-muted"><?= e(conf('tienda_nota_personalizacion')) ?></p>
    <a href="<?= url('tienda') ?>" class="btn btn-outline-mi">Conócenos</a></div>
</div></section>
