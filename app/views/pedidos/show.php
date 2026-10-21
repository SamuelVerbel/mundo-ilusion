<div class="container py-4"><a href="<?= url('pedidos') ?>" class="link-mi">← Mis pedidos</a>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"><h1 class="section-title mb-0">Pedido #<?= (int) $p['id_pedido'] ?></h1><div><?= badge_pedido($p['estado']) ?> <?= badge_pago($p['estado_pago']) ?></div></div>
<div class="row g-4 mt-1">
  <div class="col-lg-8">
    <div class="card p-3 mb-3"><h6>Productos</h6><table class="table table-sm mb-0"><thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead><tbody>
      <?php foreach ($detalles as $d): ?><tr><td><?= e($d['nombre_producto']) ?><?= $d['porcentaje_descuento'] > 0 ? ' <span class="badge bg-mi">-' . (int) $d['porcentaje_descuento'] . '%</span>' : '' ?></td><td class="text-center"><?= (int) $d['cantidad'] ?></td><td class="text-end"><?= cop($d['precio_unitario']) ?></td><td class="text-end"><?= cop($d['subtotal']) ?></td></tr><?php endforeach; ?>
      </tbody><tfoot><tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end"><?= cop($p['subtotal']) ?></td></tr>
      <?php if ($p['descuento_total'] > 0): ?><tr><td colspan="3" class="text-end text-success">Descuentos</td><td class="text-end text-success">-<?= cop($p['descuento_total']) ?></td></tr><?php endif; ?>
      <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end"><?= cop($p['total']) ?></td></tr></tfoot></table></div>
    <div class="card p-3"><h6>Seguimiento</h6><ul class="list-unstyled small mb-0"><?php foreach ($historial as $h): ?><li class="mb-1"><i class="bi bi-dot"></i> <b><?= e(ESTADOS_PEDIDO[$h['estado_nuevo']][0] ?? $h['estado_nuevo']) ?></b> · <?= fecha($h['fecha_cambio'], true) ?><?= $h['nota'] ? ' <span class="text-muted">— ' . e($h['nota']) . '</span>' : '' ?></li><?php endforeach; ?></ul></div>
  </div>
  <div class="col-lg-4">
    <div class="card p-3 mb-3"><h6>Entrega</h6><p class="small mb-1"><b><?= $p['modalidad_entrega'] === 'domicilio' ? 'Domicilio' : 'Recoges en la tienda' ?></b> · <?= fecha($p['fecha_entrega']) ?></p>
      <?php if ($p['modalidad_entrega'] === 'domicilio'): ?><p class="small mb-1"><?= e($p['dir_direccion']) ?> · <?= e($p['dir_barrio']) ?><?= $p['dir_referencia'] ? '<br><span class="text-muted">' . e($p['dir_referencia']) . '</span>' : '' ?></p><?php endif; ?>
      <?php if ($p['dedicatoria']): ?><p class="small mb-1"><i class="bi bi-chat-heart text-mi"></i> “<?= e($p['dedicatoria']) ?>”</p><?php endif; ?>
      <?php if ($p['observaciones']): ?><p class="small text-muted mb-0"><?= e($p['observaciones']) ?></p><?php endif; ?></div>
    <?php if ($puedePagar): ?>
    <div class="card p-3 mb-3 border-warning"><h6>Pago pendiente</h6><p class="small">Tienes hasta el <b><?= fecha($p['fecha_expiracion'], true) ?></b> para pagar; después el pedido se cancela automáticamente.</p>
      <form method="post" action="<?= url('pedidos/' . (int) $p['id_pedido'] . '/pagar') ?>"><?= csrf_field() ?><button class="btn btn-mi w-100"><i class="bi bi-credit-card"></i> Pagar con Wompi</button></form>
      <?php if (!$wompi): ?><div class="small text-muted mt-2">Wompi aún no está configurado (llaves en <code>.env</code>).</div><?php endif; ?>
      <?php if (APP_ENV === 'local'): ?><hr><div class="small text-muted mb-1">Solo desarrollo:</div><div class="d-flex gap-2">
        <form method="post" action="<?= url('pedidos/' . (int) $p['id_pedido'] . '/simular-pago') ?>"><?= csrf_field() ?><input type="hidden" name="resultado" value="aprobado"><button class="btn btn-sm btn-success">Simular pago aprobado</button></form>
        <form method="post" action="<?= url('pedidos/' . (int) $p['id_pedido'] . '/simular-pago') ?>"><?= csrf_field() ?><input type="hidden" name="resultado" value="rechazado"><button class="btn btn-sm btn-outline-danger">Simular rechazo</button></form></div><?php endif; ?></div>
    <?php endif; ?>
    <?php if (in_array($p['estado'], ['pendiente_pago', 'recibido'], true)): ?>
      <form method="post" action="<?= url('pedidos/' . (int) $p['id_pedido'] . '/cancelar') ?>" onsubmit="return confirm('¿Seguro que quieres cancelar este pedido?')"><?= csrf_field() ?><button class="btn btn-outline-danger w-100">Cancelar pedido</button></form><?php endif; ?>
  </div></div></div>
