<?php
class AuthController extends Controller
{
    public function loginForm(): void { if (Auth::check()) redirect(Auth::home()); $this->view('auth/login', ['title' => 'Iniciar sesión']); }

    public function login(): void
    {
        $correo = $this->post('correo'); $pass = (string) ($_POST['password'] ?? '');
        $u = $correo !== '' ? Usuario::porCorreo($correo) : null;
        $generico = 'Correo o contraseña incorrectos.';
        if ($u && Usuario::estaBloqueado($u)) $this->back(['x' => 'Tu cuenta está bloqueada temporalmente por varios intentos fallidos. Espera 15 minutos o restablece tu contraseña.'], 'login');
        if (!$u || !password_verify($pass, $u['password_hash'])) {
            if ($u && Usuario::registrarFallo($u)) { Mailer::send($u['correo'], 'Tu cuenta fue bloqueada temporalmente', Mailer::plantilla('Cuenta bloqueada', '<p>Detectamos 5 intentos fallidos de ingreso. Tu cuenta está bloqueada por 15 minutos. Si no fuiste tú, restablece tu contraseña.</p>')); }
            $this->back(['x' => $generico], 'login');
        }
        if ($u['estado'] !== 'activo') $this->back(['x' => 'Tu cuenta está desactivada. Comunícate con la tienda.'], 'login');
        Auth::login($u);
        $to = $_SESSION['intended'] ?? null; unset($_SESSION['intended']);
        if ($to && $to[0] === '/' && !str_starts_with($to, '//') && !str_contains($to, '/login')) { header('Location: ' . $to); exit; }
        redirect(Auth::home());
    }
    public function logout(): void { Auth::logout(); Session::start(); flash('success', 'Cerraste sesión correctamente.'); redirect('login'); }

    public function registroForm(): void { if (Auth::check()) redirect(''); $this->view('auth/registro', ['title' => 'Crear cuenta']); }
    public function registro(): void
    {
        $d = ['nombre' => $this->post('nombre'), 'documento' => $this->post('documento'), 'correo' => $this->post('correo'), 'telefono' => $this->post('telefono'), 'password' => (string) ($_POST['password'] ?? '')];
        $v = (new Validator())
            ->required('nombre', $d['nombre'], 'El nombre')->max('nombre', $d['nombre'], 120, 'El nombre')
            ->required('documento', $d['documento'], 'El documento')->regex('documento', $d['documento'], '/^\d{5,15}$/', 'El documento debe tener solo números (5 a 15 dígitos).')
            ->required('correo', $d['correo'], 'El correo')->email('correo', $d['correo'])
            ->required('telefono', $d['telefono'], 'El teléfono')->regex('telefono', $d['telefono'], '/^\+?\d{7,15}$/', 'El teléfono debe tener entre 7 y 15 dígitos.')
            ->password('password', $d['password'])
            ->custom('password_confirm', $d['password'] === (string) ($_POST['password_confirm'] ?? ''), 'Las contraseñas no coinciden.')
            ->custom('politica', !empty($_POST['politica']), 'Debes aceptar la política de tratamiento de datos personales.');
        if (!$v->fails()) {
            $v->custom('correo', !Usuario::correoTomado($d['correo']), 'Ya existe una cuenta con ese correo.')
              ->custom('documento', !Usuario::documentoTomado($d['documento']), 'Ya existe una cuenta con ese documento.');
        }
        if ($v->fails()) $this->back($v->errors, 'registro');
        $id = Usuario::crear($d);
        Mailer::send($d['correo'], '¡Bienvenida a ' . APP_NAME . '!', Mailer::plantilla('¡Tu cuenta fue creada!', '<p>Hola ' . e($d['nombre']) . ', ya puedes explorar el catálogo y hacer tus pedidos.</p>'));
        Auth::login(Usuario::find($id));
        flash('success', '¡Cuenta creada! Bienvenido(a) a ' . APP_NAME . '.');
        redirect('');
    }

    public function olvidoForm(): void { $this->view('auth/olvido', ['title' => 'Recuperar contraseña']); }
    public function olvido(): void
    {
        $correo = $this->post('correo');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $this->back(['correo' => 'Escribe un correo válido.'], 'recuperar');
        $u = Usuario::porCorreo($correo);
        if ($u && $u['estado'] === 'activo') {
            $t = PasswordReset::crear((int) $u['id_usuario']);
            $link = absolute_url('restablecer/' . $t);
            Mailer::send($u['correo'], 'Restablece tu contraseña', Mailer::plantilla('Restablecer contraseña', '<p>Haz clic en el botón para crear una nueva contraseña. El enlace vence en 1 hora.</p><p><a href="' . e($link) . '" style="background:#E0457B;color:#fff;padding:10px 18px;border-radius:20px;text-decoration:none">Crear nueva contraseña</a></p><p style="font-size:12px;color:#777">Si no lo solicitaste, ignora este mensaje.</p>'));
        }
        // mismo mensaje exista o no el correo: no revelamos qué correos están registrados
        flash('success', 'Si el correo está registrado, te enviamos las instrucciones para restablecer tu contraseña.');
        redirect('login');
    }
    public function resetForm(string $token): void
    {
        if (!PasswordReset::validar($token)) { flash('danger', 'El enlace no es válido o ya venció. Solicita uno nuevo.'); redirect('recuperar'); }
        $this->view('auth/reset', ['title' => 'Nueva contraseña', 'token' => $token]);
    }
    public function reset(string $token): void
    {
        $r = PasswordReset::validar($token);
        if (!$r) { flash('danger', 'El enlace no es válido o ya venció. Solicita uno nuevo.'); redirect('recuperar'); }
        $pass = (string) ($_POST['password'] ?? '');
        $v = (new Validator())->password('password', $pass)->custom('password_confirm', $pass === (string) ($_POST['password_confirm'] ?? ''), 'Las contraseñas no coinciden.');
        $u = Usuario::find((int) $r['id_usuario']);
        if (!$v->fails() && $u && password_verify($pass, $u['password_hash'])) $v->errors['password'] = 'La nueva contraseña no puede ser igual a la anterior.';
        if ($v->fails()) $this->back($v->errors, 'restablecer/' . $token);
        Usuario::cambiarPassword((int) $r['id_usuario'], $pass);
        PasswordReset::marcarUsado((int) $r['id_reset']);
        flash('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        redirect('login');
    }
}
