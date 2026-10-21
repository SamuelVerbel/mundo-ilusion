<form class="row g-2 mb-3" method="get">
  <div class="col-auto"><select name="estado" class="form-select"><option value="">Todos los estados</option><?php foreach (['pendiente' => 'Pendientes', 'respondida' => 'Respondidas', 'cerrada' => 'Cerradas'] as $k => $v): ?><option value="<?= $k ?>" <?= $estado === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
  <div class="col-auto"><select name="tipo" class="form-select"><option value="">Todos los tipos</option><?php foreach (Pqr::TIPOS as $k => $v): ?><option value="<?= $k ?>" <?= $tipo === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
  <div class="col-auto"><button class="btn btn-mi">Filtrar</button></div></form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>#</th><th>Cliente</th><th>Tipo</th><th>Asunto</th><th>Fecha</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($items as $q): ?><tr><td><?= (int) $q['id_pqr'] ?></td><td><?= e($q['cliente']) ?></td><td><?= e(Pqr::TIPOS[$q['tipo']]) ?></td><td><?= e($q['asunto']) ?></td><td><?= fecha($q['fecha_creacion'], true) ?></td>
<td><span class="badge text-bg-<?= ['pendiente' => 'warning', 'respondida' => 'success', 'cerrada' => 'secondary'][$q['estado']] ?>"><?= e(ucfirst($q['estado'])) ?></span></td>
<td class="text-end"><a class="btn btn-sm btn-outline-mi" href="<?= url('admin/pqr/' . (int) $q['id_pqr']) ?>"><?= $q['estado'] === 'pendiente' ? 'Responder' : 'Ver' ?></a></td></tr><?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="7" class="text-center text-muted py-4">No hay mensajes con estos filtros.</td></tr><?php endif; ?></tbody></table></div></div>
