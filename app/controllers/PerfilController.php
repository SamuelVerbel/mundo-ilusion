<?php
class PerfilController extends Controller
{
    public function index(): void
    {
        $this->view('perfil/index', ['title' => 'Mi perfil', 'u' => Auth::user(), 'direcciones' => Auth::is('cliente') ? Direccion::deUsuario(Auth::id()) : []]);
    }
    public function update(): void
    {
        $u = Auth::user();
        $nombre = $this->post('nombre'); $tel = $this->post('telefono'); $correo = $this->post('correo');
        $v = (new Validator())->required('nombre', $nombre, 'El nombre')->max('nombre', $nombre, 120, 'El nombre')
            ->required('telefono', $tel, 'El teléfono')->regex('telefono', $tel, '/^\+?\d{7,15}$/', 'El teléfono debe tener entre 7 y 15 dígitos.')
            ->required('correo', $correo, 'El correo')->email('correo', $correo);
        if (!$v->fails()) $v->custom('correo', !Usuario::correoTomado($correo, (int) $u['id_usuario']), 'Ese correo ya pertenece a otra cuenta.');
        if ($v->fails()) $this->back($v->errors, 'perfil');
        Usuario::actualizarPerfil((int) $u['id_usuario'], $nombre, $tel, $correo);
        flash('success', 'Tus datos fueron actualizados.');
        redirect('perfil');
    }
    public function password(): void
    {
        $u = Auth::user(); $nueva = (string) ($_POST['nueva'] ?? '');
        $v = (new Validator())->custom('actual', password_verify((string) ($_POST['actual'] ?? ''), $u['password_hash']), 'La contraseña actual no es correcta.')
            ->password('nueva', $nueva)->custom('nueva2', $nueva === (string) ($_POST['nueva2'] ?? ''), 'Las contraseñas nuevas no coinciden.');
        if ($v->fails()) $this->back($v->errors, 'perfil');
        Usuario::cambiarPassword((int) $u['id_usuario'], $nueva);
        flash('success', 'Tu contraseña fue cambiada.');
        redirect('perfil');
    }
    public function direccionCrear(): void
    {
        $dir = $this->post('direccion'); $barrio = $this->post('barrio'); $ref = $this->post('referencia');
        $v = (new Validator())->required('direccion', $dir, 'La dirección')->max('direccion', $dir, 200, 'La dirección')->required('barrio', $barrio, 'El barrio')->max('barrio', $barrio, 100, 'El barrio')->max('referencia', $ref, 200, 'La referencia');
        if ($v->fails()) $this->back($v->errors, 'perfil');
        Direccion::crear(Auth::id(), $dir, $barrio, $ref);
        flash('success', 'Dirección guardada.');
        redirect('perfil');
    }
    public function direccionDesactivar(string $id): void { Direccion::desactivar((int) $id, Auth::id()); flash('success', 'Dirección eliminada de tu lista.'); redirect('perfil'); }
}
