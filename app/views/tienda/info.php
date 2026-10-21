<div class="container py-4" style="max-width:980px"><h1 class="section-title">La tienda</h1>
  <div class="row g-4 mt-1 align-items-center">
    <div class="col-md-5"><img src="<?= asset('img/tienda.jpg') ?>" alt="Nuestra tienda" class="img-fluid rounded-4 shadow"></div>
    <div class="col-md-7"><h2 class="h3">Hecho con amor y propósito</h2><p class="lead text-muted"><?= e(conf('tienda_mensaje')) ?></p><p class="text-muted"><?= e(conf('tienda_nota_personalizacion')) ?></p></div>
  </div>
  <div class="row g-4 mt-2">
    <div class="col-md-6"><div class="card p-4 h-100"><h5>Contacto</h5>
      <?php foreach ([['bi-geo-alt', 'tienda_direccion'], ['bi-clock', 'tienda_horarios'], ['bi-telephone', 'tienda_telefono'], ['bi-whatsapp', 'tienda_whatsapp'], ['bi-instagram', 'tienda_instagram'], ['bi-facebook', 'tienda_facebook']] as [$ic, $k]): if (conf($k)): ?>
        <p class="mb-1"><i class="bi <?= $ic ?> text-mi"></i> <?= e(conf($k)) ?></p><?php endif; endforeach; ?>
      <?php if (!conf('tienda_whatsapp') && !conf('tienda_telefono')): ?><p class="small text-muted mb-0">Escríbenos desde la sección de <a class="link-mi" href="<?= url('pqr') ?>">PQR</a>.</p><?php endif; ?></div></div>
    <div class="col-md-6"><div class="card p-4 h-100"><h5>Políticas</h5>
      <h6 class="mt-2">Entrega</h6><p class="small"><?= e(conf('politica_entrega')) ?></p>
      <h6>Cambios y devoluciones</h6><p class="small"><?= e(conf('politica_cambios_devoluciones')) ?></p>
      <h6>Tratamiento de datos</h6><p class="small mb-0"><?= e(conf('politica_datos')) ?></p></div></div>
  </div></div>
