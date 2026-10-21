<form method="post" action="<?= url('admin/configuracion') ?>"><?= csrf_field() ?>
<p class="text-muted">Aquí cambias los datos que ve el cliente en la tienda y los parámetros de los pedidos. Si dejas un dato vacío, simplemente no se muestra.</p>
<?php foreach ($grupos as $titulo => $claves): ?>
<div class="card p-4 mb-3"><h5 class="mb-3"><?= e($titulo) ?></h5><div class="row g-3">
  <?php foreach ($claves as $k): $f = $filas[$k] ?? ['valor' => '', 'descripcion' => $k]; $largo = in_array($k, ['tienda_mensaje', 'tienda_nota_personalizacion', 'aviso_licor', 'politica_entrega', 'politica_cambios_devoluciones', 'politica_datos'], true); ?>
    <div class="<?= $largo ? 'col-12' : 'col-md-6' ?>"><label class="form-label fw-bold small"><?= e($f['descripcion']) ?></label>
      <?php if ($largo): ?><textarea name="<?= e($k) ?>" rows="2" maxlength="1000" class="form-control"><?= e(old($k, $f['valor'])) ?></textarea>
      <?php else: ?><input name="<?= e($k) ?>" maxlength="500" class="form-control" value="<?= e(old($k, $f['valor'])) ?>"><?php endif; ?></div>
  <?php endforeach; ?></div></div>
<?php endforeach; ?>
<button class="btn btn-mi btn-lg">Guardar configuración</button></form>
