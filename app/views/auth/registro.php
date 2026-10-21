<div class="container py-5" style="max-width:560px"><div class="card p-4 shadow-sm"><div class="text-center mb-2"><img src="<?= asset('img/logo.png') ?>" alt="Mundo Ilusión" class="auth-logo"></div><h1 class="h3 section-title text-center d-block">Crear cuenta</h1>
  <form method="post" action="<?= url('registro') ?>" class="row g-3 mt-1"><?= csrf_field() ?>
    <div class="col-12"><label class="form-label">Nombre completo</label><input name="nombre" class="form-control" value="<?= e(old('nombre')) ?>" required maxlength="120"></div>
    <div class="col-md-6"><label class="form-label">Documento</label><input name="documento" class="form-control" value="<?= e(old('documento')) ?>" required inputmode="numeric" pattern="\d{5,15}"></div>
    <div class="col-md-6"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" value="<?= e(old('telefono')) ?>" required inputmode="tel"></div>
    <div class="col-12"><label class="form-label">Correo</label><input type="email" name="correo" class="form-control" value="<?= e(old('correo')) ?>" required></div>
    <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" required minlength="8"><div class="form-text">Mínimo 8 caracteres, con letras y números.</div></div>
    <div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" name="password_confirm" class="form-control" required></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="politica" id="pol" value="1" required>
      <label class="form-check-label small" for="pol">Acepto la política de tratamiento de datos personales. <a href="#" data-bs-toggle="collapse" data-bs-target="#polTxt" class="link-mi">Leer</a></label></div>
      <div class="collapse small text-muted mt-2" id="polTxt"><?= e(conf('politica_datos')) ?></div></div>
    <div class="col-12"><button class="btn btn-mi w-100">Crear mi cuenta</button></div></form>
  <p class="text-center small mt-3 mb-0">¿Ya tienes cuenta? <a class="link-mi" href="<?= url('login') ?>">Inicia sesión</a></p></div></div>
