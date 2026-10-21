<?php $wa = whatsapp_url('Hola Mundo Ilusión, quiero información sobre "' . $p['nombre'] . '"'); $licor = contiene_licor($p['descripcion']); ?>
<div class="container py-4">
  <a href="<?= url('catalogo') ?>" class="link-mi">← Volver al catálogo</a>
  <div class="row g-5 mt-1">
    <div class="col-md-5">
      <div class="detalle-img"><img id="imgPrincipal" src="<?= img_producto($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>"></div>
      <?php if (count($p['imagenes']) > 1): ?><div class="d-flex gap-2 mt-2 flex-wrap"><?php foreach ($p['imagenes'] as $im): ?>
        <img src="<?= img_producto($im['ruta']) ?>" class="thumb" alt="" data-full="<?= img_producto($im['ruta']) ?>"><?php endforeach; ?></div>
        <p class="small text-muted mt-2 mb-0">Imágenes de referencia: cada detalle se personaliza según tu temática.</p><?php endif; ?>
    </div>
    <div class="col-md-7">
      <div class="mb-1"><?php foreach ($p['categorias'] as $c): ?><a class="badge rounded-pill text-bg-light border text-decoration-none" href="<?= url('catalogo?categoria=' . (int) $c['id_categoria']) ?>"><?= e($c['nombre']) ?></a> <?php endforeach; ?></div>
      <h1 class="section-title"><?= e($p['nombre']) ?></h1>
      <div class="mb-3"><span class="precio fs-2"><?= cop($p['precio_final']) ?></span>
        <?php if ($p['con_descuento']): ?><span class="text-muted text-decoration-line-through ms-2"><?= cop($p['precio']) ?></span> <span class="badge bg-mi">-<?= (int) $p['descuento'] ?>%</span><?php endif; ?></div>
      <div class="descripcion"><?= descripcion_html($p['descripcion']) ?></div>
      <?php if ($licor && conf('aviso_licor')): ?><div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle"></i> <?= e(conf('aviso_licor')) ?></div><?php endif; ?>
      <?php if (conf('tienda_nota_personalizacion')): ?><div class="nota-personalizacion"><i class="bi bi-palette"></i> <?= e(conf('tienda_nota_personalizacion')) ?> <span class="d-block mt-1">Cuéntanos tu temática en las <b>observaciones</b> al finalizar el pedido.</span></div><?php endif; ?>
      <p class="mb-3"><?= (int) $p['disponible'] ? '<span class="badge text-bg-success">Disponible</span>' : '<span class="badge text-bg-secondary">No disponible por ahora</span>' ?></p>
      <form method="post" action="<?= url('carrito/agregar') ?>" class="d-flex gap-2 align-items-center flex-wrap"><?= csrf_field() ?>
        <input type="hidden" name="id_producto" value="<?= (int) $p['id_producto'] ?>"><input type="hidden" name="ir_carrito" value="1">
        <input type="number" name="cantidad" min="1" max="99" value="1" class="form-control" style="width:90px" <?= (int) $p['disponible'] ? '' : 'disabled' ?>>
        <button class="btn btn-mi btn-lg" <?= (int) $p['disponible'] ? '' : 'disabled' ?>><i class="bi bi-cart-plus"></i> Agregar al carrito</button>
        <?php if ($wa): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener" class="btn btn-outline-success btn-lg"><i class="bi bi-whatsapp"></i> Preguntar</a><?php endif; ?></form>
      <?php $titulo = 'Agrega un detalle extra'; require ROOT_PATH . '/app/views/partials/extras.php'; ?>
    </div>
  </div>
  <?php if ($relacionados): ?><h3 class="section-title mt-5 mb-3">También te puede gustar</h3><div class="row g-4"><?php foreach ($relacionados as $p) require ROOT_PATH . '/app/views/partials/producto_card.php'; ?></div><?php endif; ?>
</div>
