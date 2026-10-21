<div class="container py-4">
  <h1 class="section-title">Catálogo</h1>
  <div class="chips my-3"><a href="<?= url('catalogo') ?>" class="chip <?= !$f['categoria'] ? 'active' : '' ?>">Todo</a>
    <?php foreach ($categorias as $c): ?><a href="<?= url('catalogo?categoria=' . (int) $c['id_categoria']) ?>" class="chip <?= (int) $f['categoria'] === (int) $c['id_categoria'] ? 'active' : '' ?>"><?= e($c['nombre']) ?> <small>(<?= (int) $c['total'] ?>)</small></a><?php endforeach; ?></div>
  <form class="row g-2 mb-3" method="get" action="<?= url('catalogo') ?>">
    <input type="hidden" name="categoria" value="<?= (int) $f['categoria'] ?: '' ?>">
    <div class="col-md-6"><input class="form-control" name="q" value="<?= e($f['q']) ?>" placeholder="Buscar por nombre…"></div>
    <div class="col-md-4"><select class="form-select" name="orden" onchange="this.form.submit()"><?php foreach (['' => 'Ordenar por nombre', 'precio_asc' => 'Precio: menor a mayor', 'precio_desc' => 'Precio: mayor a menor', 'nuevos' => 'Más nuevos'] as $k => $v): ?><option value="<?= $k ?>" <?= $f['orden'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-mi"><i class="bi bi-search"></i> Buscar</button></div>
  </form>
  <p class="text-muted small"><?= (int) $r['total'] ?> producto(s) encontrado(s)</p>
  <?php if (!$r['items']): ?><div class="text-center py-5 text-muted"><div style="font-size:3rem">🔍</div><p>No encontramos productos con esos filtros.</p><a href="<?= url('catalogo') ?>" class="btn btn-outline-mi">Ver todo el catálogo</a></div><?php endif; ?>
  <div class="row g-4"><?php foreach ($r['items'] as $p) require ROOT_PATH . '/app/views/partials/producto_card.php'; ?></div>
  <?php $base = 'catalogo'; $qs = ['q' => $f['q'], 'categoria' => $f['categoria'], 'orden' => $f['orden']]; require ROOT_PATH . '/app/views/partials/paginacion.php'; ?>
</div>
