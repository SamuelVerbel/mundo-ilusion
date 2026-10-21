<?php $u = Auth::user(); $isAdmin = Auth::is('administrador'); $path = $_SERVER['REQUEST_URI'];
$item = fn($href, $icon, $txt) => '<a class="nav-link ' . (str_contains($path, $href) && ($href !== 'admin' || preg_match('#/admin/?(\?.*)?$#', $path)) ? 'active' : '') . '" href="' . url($href) . '"><i class="bi bi-' . $icon . '"></i> ' . $txt . '</a>'; ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light"><head><?php require ROOT_PATH . '/app/views/partials/tema_head.php'; ?><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Panel') ?> · Panel Mundo Ilusión</title>
<link href="<?= asset('assets/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= asset('assets/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link rel="icon" type="image/png" href="<?= asset('img/favicon-64.png') ?>"><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet"><link href="<?= asset('css/app.css') ?>" rel="stylesheet"></head>
<body class="admin-body">
<div class="d-flex">
  <aside class="admin-side p-3">
    <a class="d-flex align-items-center gap-2 mb-4 text-decoration-none" href="<?= url('admin') ?>"><img src="<?= asset('img/logo.png') ?>" alt="" style="width:42px;height:42px;border-radius:50%"><span class="mi-logo text-white fs-5">Mundo <span>Ilusión</span></span></a>
    <nav class="nav flex-column gap-1">
      <?= $item('admin', 'speedometer2', 'Resumen') ?>
      <?= $item('admin/pedidos', 'bag-heart', 'Pedidos') ?>
      <?php if ($isAdmin): ?>
        <?= $item('admin/productos', 'gift', 'Productos') ?>
        <?= $item('admin/categorias', 'tags', 'Categorías') ?>
        <?= $item('admin/configuracion', 'gear', 'Configuración') ?>
        <?= $item('admin/promociones', 'percent', 'Promociones') ?>
        <?= $item('admin/empleados', 'people', 'Empleados') ?>
        <?php $pq = Pqr::pendientes(); ?>
        <?= str_replace('</a>', ($pq ? ' <span class="badge rounded-pill bg-mi">' . $pq . '</span>' : '') . '</a>', $item('admin/pqr', 'chat-heart', 'PQR')) ?>
        <?= $item('admin/reportes', 'graph-up', 'Reportes') ?>
      <?php endif; ?>
    </nav>
    <hr class="border-secondary">
    <a class="nav-link small" href="<?= url() ?>" target="_blank"><i class="bi bi-shop"></i> Ver tienda</a>
    <a class="nav-link small" href="<?= url('perfil') ?>"><i class="bi bi-person"></i> Mi perfil</a>
    <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="nav-link small btn btn-link text-start w-100"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</button></form>
  </aside>
  <main class="flex-grow-1 p-4 admin-main">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0"><?= e($title ?? '') ?></h1>
      <div class="d-flex align-items-center gap-2"><?php require ROOT_PATH . '/app/views/partials/tema_toggle.php'; ?><span class="badge text-bg-light border"><i class="bi bi-person-badge"></i> <?= e($u['nombre']) ?> · <?= e($u['rol']) ?></span></div>
    </div>
    <?php foreach (Session::pullFlash() as [$t, $m]): ?><div class="alert alert-<?= e($t) ?> alert-dismissible fade show"><?= e($m) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endforeach; ?>
    <?= $content ?>
  </main>
</div>
<script src="<?= asset('assets/bootstrap/js/bootstrap.bundle.min.js') ?>"></script><script src="<?= asset('js/app.js') ?>"></script>
</body></html>
