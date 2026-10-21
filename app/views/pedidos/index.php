<div class="container py-4"><h1 class="section-title">Mis pedidos</h1>
<?php if (!$pedidos): ?><div class="text-center py-5 text-muted"><div style="font-size:3rem">🛍️</div><p>Aún no tienes pedidos.</p><a href="<?= url('catalogo') ?>" class="btn btn-mi">Ir al catálogo</a></div>
<?php else: ?><div class="table-responsive mt-3"><table class="table align-middle bg-white shadow-sm rounded"><thead><tr><th>#</th><th>Fecha</th><th>Entrega</th><th>Total</th><th>Estado</th><th>Pago</th><th></th></tr></thead><tbody>
<?php foreach ($pedidos as $p): ?><tr><td>#<?= (int) $p['id_pedido'] ?></td><td><?= fecha($p['fecha_pedido']) ?></td><td><?= fecha($p['fecha_entrega']) ?> · <?= $p['modalidad_entrega'] === 'domicilio' ? 'Domicilio' : 'Recogida' ?></td>
  <td><?= cop($p['total']) ?></td><td><?= badge_pedido($p['estado']) ?></td><td><?= badge_pago($p['estado_pago']) ?></td><td class="text-end"><a class="btn btn-sm btn-outline-mi" href="<?= url('pedidos/' . (int) $p['id_pedido']) ?>">Ver</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
