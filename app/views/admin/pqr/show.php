<div class="row g-3"><div class="col-lg-7"><div class="card p-4">
  <div class="d-flex justify-content-between"><h5 class="mb-1"><?= e($q['asunto']) ?></h5><span class="badge text-bg-<?= ['pendiente' => 'warning', 'respondida' => 'success', 'cerrada' => 'secondary'][$q['estado']] ?> align-self-start"><?= e(ucfirst($q['estado'])) ?></span></div>
  <small class="text-muted"><?= e(Pqr::TIPOS[$q['tipo']]) ?> · <?= fecha($q['fecha_creacion'], true) ?></small>
  <p class="mt-3" style="white-space:pre-wrap"><?= e($q['mensaje']) ?></p>
  <?php if ($q['respuesta']): ?><div class="alert alert-light border"><b>Respuesta<?= $q['respondio'] ? ' de ' . e($q['respondio']) : '' ?> (<?= fecha($q['fecha_respuesta'], true) ?>):</b><p class="mb-0 mt-1" style="white-space:pre-wrap"><?= e($q['respuesta']) ?></p></div><?php endif; ?>
  <?php if ($q['estado'] === 'pendiente'): ?>
    <form method="post" action="<?= url('admin/pqr/' . (int) $q['id_pqr'] . '/responder') ?>"><?= csrf_field() ?><label class="form-label">Tu respuesta (se envía al correo del cliente)</label>
      <textarea name="respuesta" rows="5" class="form-control mb-3" maxlength="2000" required><?= e(old('respuesta')) ?></textarea><button class="btn btn-mi"><i class="bi bi-send"></i> Enviar respuesta</button></form>
  <?php elseif ($q['estado'] === 'respondida'): ?>
    <form method="post" action="<?= url('admin/pqr/' . (int) $q['id_pqr'] . '/cerrar') ?>"><?= csrf_field() ?><button class="btn btn-outline-secondary">Cerrar caso</button></form>
  <?php endif; ?></div><a href="<?= url('admin/pqr') ?>" class="btn btn-link mt-2">← Volver</a></div>
  <div class="col-lg-5"><div class="card p-4"><h6>Cliente</h6><p class="mb-1"><b><?= e($q['cliente']) ?></b></p><p class="mb-1"><i class="bi bi-envelope"></i> <?= e($q['cliente_correo']) ?></p><p class="mb-0"><i class="bi bi-telephone"></i> <?= e($q['cliente_tel']) ?></p></div></div></div>
