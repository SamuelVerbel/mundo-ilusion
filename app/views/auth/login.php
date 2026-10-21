<div class="container py-5" style="max-width:440px"><div class="card p-4 shadow-sm"><div class="text-center mb-2"><img src="<?= asset('img/logo.png') ?>" alt="Mundo Ilusión" class="auth-logo"></div><h1 class="h3 section-title text-center d-block">Iniciar sesión</h1>
  <form method="post" action="<?= url('login') ?>" class="mt-3"><?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Correo</label><input type="email" name="correo" class="form-control" value="<?= e(old('correo')) ?>" required autofocus></div>
    <div class="mb-2"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" required></div>
    <div class="mb-3 text-end"><a class="small link-mi" href="<?= url('recuperar') ?>">¿Olvidaste tu contraseña?</a></div>
    <button class="btn btn-mi w-100">Ingresar</button></form>
  <p class="text-center small mt-3 mb-0">¿Aún no tienes cuenta? <a class="link-mi" href="<?= url('registro') ?>">Regístrate</a></p></div></div>
