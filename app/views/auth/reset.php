<div class="container py-5" style="max-width:440px"><div class="card p-4 shadow-sm"><div class="text-center mb-2"><img src="<?= asset('img/logo.png') ?>" alt="Mundo Ilusión" class="auth-logo"></div><h1 class="h3 section-title text-center d-block">Nueva contraseña</h1>
  <form method="post" action="<?= url('restablecer/' . e($token)) ?>" class="mt-3"><?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Nueva contraseña</label><input type="password" name="password" class="form-control" required minlength="8"></div>
    <div class="mb-3"><label class="form-label">Confirmar contraseña</label><input type="password" name="password_confirm" class="form-control" required></div>
    <button class="btn btn-mi w-100">Guardar contraseña</button></form></div></div>
