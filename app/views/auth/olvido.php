<div class="container py-5" style="max-width:440px"><div class="card p-4 shadow-sm"><div class="text-center mb-2"><img src="<?= asset('img/logo.png') ?>" alt="Mundo Ilusión" class="auth-logo"></div><h1 class="h3 section-title text-center d-block">Recuperar contraseña</h1>
  <p class="text-muted small mt-3">Escribe tu correo y te enviaremos un enlace para crear una nueva contraseña.</p>
  <form method="post" action="<?= url('recuperar') ?>"><?= csrf_field() ?><div class="mb-3"><input type="email" name="correo" class="form-control" placeholder="tu@correo.com" required autofocus></div>
    <button class="btn btn-mi w-100">Enviar enlace</button></form><p class="text-center small mt-3 mb-0"><a class="link-mi" href="<?= url('login') ?>">Volver a iniciar sesión</a></p></div></div>
