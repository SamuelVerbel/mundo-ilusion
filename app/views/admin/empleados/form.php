<div class="card p-4" style="max-width:640px"><form method="post" action="<?= url($e ? 'admin/empleados/' . (int) $e['id_usuario'] : 'admin/empleados') ?>" class="row g-3"><?= csrf_field() ?>
  <div class="col-md-6"><label class="form-label">Documento</label><input name="documento" class="form-control" maxlength="20" required value="<?= e(old('documento', $e['documento'] ?? '')) ?>"></div>
  <div class="col-md-6"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" maxlength="20" required value="<?= e(old('telefono', $e['telefono'] ?? '')) ?>"></div>
  <div class="col-12"><label class="form-label">Nombre completo</label><input name="nombre" class="form-control" maxlength="120" required value="<?= e(old('nombre', $e['nombre'] ?? '')) ?>"></div>
  <div class="col-12"><label class="form-label">Correo</label><input name="correo" type="email" class="form-control" maxlength="150" required value="<?= e(old('correo', $e['correo'] ?? '')) ?>"></div>
  <?php if (!$e): ?><div class="col-12"><div class="alert alert-info mb-0 small">Se generará una contraseña temporal: la verás una sola vez al guardar y también se envía al correo del empleado.</div></div><?php endif; ?>
  <div class="col-12"><button class="btn btn-mi">Guardar</button> <a href="<?= url('admin/empleados') ?>" class="btn btn-link">Cancelar</a></div></form></div>
