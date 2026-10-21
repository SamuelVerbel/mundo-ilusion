<div class="container py-5 text-center">
  <div style="font-size:4.5rem"><?= $code === 404 ? '🎈' : ($code === 403 ? '🔒' : '🛠️') ?></div>
  <h1 class="section-title d-inline-block"><?= $code === 404 ? 'Esta página se fue volando' : "Algo no salió bien ($code)" ?></h1>
  <p class="text-muted mt-3"><?= e($msg ?: 'Inténtalo de nuevo en unos minutos.') ?></p>
  <a href="<?= url() ?>" class="btn btn-mi mt-2">Volver al inicio</a>
</div>
