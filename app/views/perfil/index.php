<div class="container py-4" style="max-width:900px"><h1 class="section-title">Mi perfil</h1>
  <div class="row g-4 mt-1">
    <div class="col-md-6"><div class="card p-4"><h5>Mis datos</h5>
      <form method="post" action="<?= url('perfil') ?>" class="mt-2"><?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small">Documento</label><input class="form-control" value="<?= e($u['documento']) ?>" disabled></div>
        <div class="mb-2"><label class="form-label small">Nombre</label><input name="nombre" class="form-control" value="<?= e(old('nombre', $u['nombre'])) ?>" required></div>
        <div class="mb-2"><label class="form-label small">Correo</label><input type="email" name="correo" class="form-control" value="<?= e(old('correo', $u['correo'])) ?>" required></div>
        <div class="mb-3"><label class="form-label small">Teléfono</label><input name="telefono" class="form-control" value="<?= e(old('telefono', $u['telefono'])) ?>" required></div>
        <button class="btn btn-mi">Guardar cambios</button></form></div></div>
    <div class="col-md-6"><div class="card p-4"><h5>Cambiar contraseña</h5>
      <form method="post" action="<?= url('perfil/password') ?>" class="mt-2"><?= csrf_field() ?>
        <div class="mb-2"><input type="password" name="actual" class="form-control" placeholder="Contraseña actual" required></div>
        <div class="mb-2"><input type="password" name="nueva" class="form-control" placeholder="Nueva contraseña" required minlength="8"></div>
        <div class="mb-3"><input type="password" name="nueva2" class="form-control" placeholder="Repite la nueva contraseña" required></div>
        <button class="btn btn-outline-mi">Cambiar contraseña</button></form></div></div>
    <?php if (Auth::is('cliente')): ?>
    <div class="col-12"><div class="card p-4"><h5>Mis direcciones de entrega</h5>
      <?php foreach ($direcciones as $d): ?><div class="d-flex justify-content-between align-items-center border-bottom py-2">
        <div><strong><?= e($d['direccion']) ?></strong> · <?= e($d['barrio']) ?><?= $d['referencia'] ? '<br><small class="text-muted">' . e($d['referencia']) . '</small>' : '' ?></div>
        <form method="post" action="<?= url('perfil/direcciones/' . (int) $d['id_direccion'] . '/desactivar') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" title="Quitar"><i class="bi bi-trash"></i></button></form></div><?php endforeach; ?>
      <?php if (!$direcciones): ?><p class="text-muted small">Aún no tienes direcciones guardadas.</p><?php endif; ?>
      <form method="post" action="<?= url('perfil/direcciones') ?>" class="row g-2 mt-2"><?= csrf_field() ?>
        <div class="col-md-4"><input name="direccion" class="form-control" placeholder="Dirección (calle, número, apto)" required></div>
        <div class="col-md-3"><input name="barrio" class="form-control" placeholder="Barrio" required></div>
        <div class="col-md-3"><input name="referencia" class="form-control" placeholder="Referencia (opcional)"></div>
        <div class="col-md-2 d-grid"><button class="btn btn-mi">Agregar</button></div></form></div></div>
    <?php endif; ?>
  </div></div>
