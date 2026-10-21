<?php $cats = Categoria::activas(); $nCarrito = Auth::is('cliente') ? Carrito::contar(Auth::id()) : 0; $u = Auth::user(); $wa = whatsapp_url('Hola Mundo Ilusión, quiero más información'); ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <?php require ROOT_PATH . '/app/views/partials/tema_head.php'; ?>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? APP_NAME) ?></title>
  <meta name="description" content="<?= e(conf('tienda_mensaje')) ?>"><meta name="theme-color" content="#F98BBF"><meta name="color-scheme" content="light dark">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/favicon-32.png') ?>"><link rel="icon" type="image/png" sizes="64x64" href="<?= asset('img/favicon-64.png') ?>">
  <link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= asset('assets/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= asset('assets/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top mi-nav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url() ?>"><img src="<?= asset('img/logo.png') ?>" alt="Mundo Ilusión" class="nav-logo"><span class="mi-logo">Mundo <span>Ilusión</span></span></a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menú"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="<?= url() ?>">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('catalogo') ?>">Catálogo</a></li>
        <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categorías</a>
          <ul class="dropdown-menu">
            <?php foreach ($cats as $c): ?><li><a class="dropdown-item" href="<?= url('catalogo?categoria=' . (int) $c['id_categoria']) ?>"><?= e($c['nombre']) ?> <small class="text-muted">(<?= (int) $c['total'] ?>)</small></a></li><?php endforeach; ?>
          </ul></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('promociones') ?>">Promociones</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('tienda') ?>">La tienda</a></li>
      </ul>
      <div class="d-flex gap-2 align-items-center">
        <?php require ROOT_PATH . '/app/views/partials/tema_toggle.php'; ?>
        <?php if ($u): ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-mi dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-heart"></i> <?= e(explode(' ', $u['nombre'])[0]) ?></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (Auth::isStaff()): ?><li><a class="dropdown-item" href="<?= url('admin') ?>"><i class="bi bi-speedometer2"></i> Panel de gestión</a></li><?php endif; ?>
              <li><a class="dropdown-item" href="<?= url('perfil') ?>"><i class="bi bi-person"></i> Mi perfil</a></li>
              <?php if (Auth::is('cliente')): ?>
                <li><a class="dropdown-item" href="<?= url('pedidos') ?>"><i class="bi bi-bag-heart"></i> Mis pedidos</a></li>
                <li><a class="dropdown-item" href="<?= url('pqr') ?>"><i class="bi bi-chat-heart"></i> PQR</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form></li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn btn-sm btn-outline-mi" href="<?= url('login') ?>">Ingresar</a>
          <a class="btn btn-sm btn-mi" href="<?= url('registro') ?>">Registrarme</a>
        <?php endif; ?>
        <?php if (!$u || Auth::is('cliente')): ?>
          <a class="btn btn-sm btn-light position-relative" href="<?= url('carrito') ?>" title="Carrito"><i class="bi bi-cart3"></i>
            <?php if ($nCarrito): ?><span class="badge rounded-pill bg-mi position-absolute top-0 start-100 translate-middle"><?= $nCarrito ?></span><?php endif; ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container mt-3"><?php foreach (Session::pullFlash() as [$t, $m]): ?>
  <div class="alert alert-<?= e($t) ?> alert-dismissible fade show" role="alert"><?= e($m) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; ?></div>

<main><?= $content ?></main>

<?php if ($wa): ?><a href="<?= e($wa) ?>" class="wa-float" target="_blank" rel="noopener" title="Escríbenos por WhatsApp"><i class="bi bi-whatsapp"></i></a><?php endif; ?>

<?php if (Chatbot::enabled()) require ROOT_PATH . '/app/views/partials/chatbot.php'; ?>

<footer class="mi-footer mt-5">
  <div class="container py-5"><div class="row g-4">
    <div class="col-md-4"><img src="<?= asset('img/logo-wordmark.png') ?>" alt="Mundo Ilusión" class="footer-logo mb-2"><p class="small opacity-75 mb-0"><?= e(conf('tienda_mensaje')) ?></p></div>
    <div class="col-md-4"><h6>Explora</h6><ul class="list-unstyled small">
      <li><a href="<?= url('catalogo') ?>">Catálogo</a></li><li><a href="<?= url('promociones') ?>">Promociones</a></li><li><a href="<?= url('tienda') ?>">Información de la tienda</a></li><li><a href="<?= url('pqr') ?>">Peticiones, quejas y sugerencias</a></li></ul></div>
    <div class="col-md-4"><h6>Contacto</h6>
      <?php if (conf('tienda_direccion')): ?><p class="small opacity-75 mb-1"><i class="bi bi-geo-alt"></i> <?= e(conf('tienda_direccion')) ?></p><?php endif; ?>
      <?php if (conf('tienda_horarios')): ?><p class="small opacity-75 mb-1"><i class="bi bi-clock"></i> <?= e(conf('tienda_horarios')) ?></p><?php endif; ?>
      <?php if (conf('tienda_whatsapp')): ?><p class="small opacity-75 mb-1"><i class="bi bi-whatsapp"></i> <?= e(conf('tienda_whatsapp')) ?></p><?php endif; ?>
      <?php if (conf('tienda_instagram')): ?><p class="small opacity-75 mb-0"><i class="bi bi-instagram"></i> <?= e(conf('tienda_instagram')) ?></p><?php endif; ?></div>
  </div><hr class="opacity-25"><p class="small text-center opacity-50 mb-0">© <?= date('Y') ?> Mundo Ilusión · Tienda de regalos</p></div>
</footer>
<script src="<?= asset('assets/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
