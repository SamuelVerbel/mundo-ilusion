<div class="container py-4"><h1 class="section-title">Finalizar pedido</h1>
<form method="post" action="<?= url('checkout') ?>" class="row g-4 mt-1" id="formCheckout"><?= csrf_field() ?>
  <div class="col-lg-7">
    <div class="card p-4 mb-3"><h5>1. ¿Cómo quieres recibirlo?</h5>
      <div class="d-flex gap-3 flex-wrap mt-2">
        <div class="form-check"><input class="form-check-input" type="radio" name="modalidad" id="m1" value="domicilio" <?= old('modalidad', 'domicilio') === 'domicilio' ? 'checked' : '' ?>><label class="form-check-label" for="m1"><i class="bi bi-truck"></i> Domicilio</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="modalidad" id="m2" value="recogida" <?= old('modalidad') === 'recogida' ? 'checked' : '' ?>><label class="form-check-label" for="m2"><i class="bi bi-shop"></i> Recoger en la tienda</label></div></div>
      <div id="bloqueDireccion" class="mt-3"><label class="form-label">Dirección de entrega</label>
        <select name="id_direccion" id="selDir" class="form-select">
          <?php foreach ($direcciones as $d): ?><option value="<?= (int) $d['id_direccion'] ?>"><?= e($d['direccion'] . ' · ' . $d['barrio']) ?></option><?php endforeach; ?>
          <option value="nueva" <?= !$direcciones ? 'selected' : '' ?>>➕ Usar una dirección nueva</option></select>
        <div id="dirNueva" class="row g-2 mt-1"><div class="col-md-6"><input name="direccion" class="form-control" placeholder="Dirección" value="<?= e(old('direccion')) ?>"></div>
          <div class="col-md-3"><input name="barrio" class="form-control" placeholder="Barrio" value="<?= e(old('barrio')) ?>"></div><div class="col-md-3"><input name="referencia" class="form-control" placeholder="Referencia" value="<?= e(old('referencia')) ?>"></div></div></div></div>
    <div class="card p-4 mb-3"><h5>2. ¿Para qué fecha?</h5>
      <input type="date" name="fecha_entrega" class="form-control" style="max-width:240px" min="<?= e($minFecha) ?>" value="<?= e(old('fecha_entrega', $minFecha)) ?>" required>
      <div class="form-text">Anticipación mínima: <?= (int) conf('anticipacion_horas', 24) ?> horas.</div></div>
    <div class="card p-4"><h5>3. Personaliza tu detalle</h5><p class="small text-muted">Todos nuestros detalles son personalizados: cuéntanos la temática, colores o personaje y el texto de la tarjeta.</p>
      <textarea name="dedicatoria" class="form-control" rows="2" maxlength="255" placeholder="Dedicatoria: mensaje que irá en la tarjeta (opcional)"><?= e(old('dedicatoria')) ?></textarea>
      <textarea name="observaciones" class="form-control mt-2" rows="2" maxlength="500" placeholder="Temática, colores, nombre o edad a destacar, indicaciones de entrega… (opcional)"><?= e(old('observaciones')) ?></textarea></div>
  </div>
  <div class="col-lg-5"><div class="card p-4"><h5>Resumen del pedido</h5>
    <?php foreach ($items as $i): ?><div class="d-flex justify-content-between small py-1"><span><?= e($i['nombre']) ?> × <?= (int) $i['cantidad'] ?></span><span><?= cop($i['subtotal']) ?></span></div><?php endforeach; ?>
    <hr><div class="d-flex justify-content-between"><span>Subtotal</span><span><?= cop($r['subtotal']) ?></span></div>
    <?php if ($r['descuento'] > 0): ?><div class="d-flex justify-content-between text-success"><span>Descuentos</span><span>-<?= cop($r['descuento']) ?></span></div><?php endif; ?>
    <div class="d-flex justify-content-between fs-5 fw-bold mt-1"><span>Total</span><span class="precio"><?= cop($r['total']) ?></span></div>
    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="politicas" id="pol" value="1" required>
      <label class="form-check-label small" for="pol">Acepto las políticas de entrega, cambios y devoluciones. <a href="#" class="link-mi" data-bs-toggle="collapse" data-bs-target="#polT">Leer</a></label></div>
    <div class="collapse small text-muted mt-2" id="polT"><b>Entrega:</b> <?= e(conf('politica_entrega')) ?><br><b>Cambios y devoluciones:</b> <?= e(conf('politica_cambios_devoluciones')) ?></div>
    <button class="btn btn-mi btn-lg mt-3">Confirmar pedido</button><p class="small text-muted mt-2 mb-0">Después de confirmar podrás pagar en línea con Wompi.</p></div></div>
</form></div>
