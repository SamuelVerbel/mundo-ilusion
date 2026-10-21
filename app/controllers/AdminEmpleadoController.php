<?php
class AdminEmpleadoController extends AdminController
{
    public function index(): void { $this->adminView('admin/empleados/index', ['title' => 'Empleados', 'emps' => Usuario::empleados()]); }
    public function nuevo(): void { $this->adminView('admin/empleados/form', ['title' => 'Nuevo empleado', 'e' => null]); }
    public function editar(string $id): void
    {
        $e = Usuario::empleado((int) $id); if (!$e) abort(404, 'Empleado no encontrado.');
        $this->adminView('admin/empleados/form', ['title' => 'Editar empleado', 'e' => $e]);
    }
    private function datos(?int $id): array
    {
        $d = ['documento' => $this->post('documento'), 'nombre' => $this->post('nombre'), 'correo' => $this->post('correo'), 'telefono' => $this->post('telefono')];
        $v = (new Validator())->required('nombre', $d['nombre'], 'El nombre')->max('nombre', $d['nombre'], 120, 'El nombre')
            ->regex('documento', $d['documento'], '/^[0-9A-Za-z]{5,20}$/', 'El documento debe tener entre 5 y 20 letras o números.')
            ->email('correo', $d['correo'])->regex('telefono', $d['telefono'], '/^[0-9+ ]{7,20}$/', 'El teléfono no es válido.');
        if (!$v->fails()) $v->custom('correo', !Usuario::correoTomado($d['correo'], (int) $id), 'Ya existe un usuario con ese correo.')->custom('documento', !Usuario::documentoTomado($d['documento'], (int) $id), 'Ya existe un usuario con ese documento.');
        if ($v->fails()) $this->back($v->errors, $id ? "admin/empleados/$id/editar" : 'admin/empleados/nuevo');
        return $d;
    }
    public function crear(): void
    {
        $d = $this->datos(null); $pass = Usuario::passwordTemporal(); $d['password'] = $pass;
        Usuario::crear($d, 'empleado');
        Mailer::send($d['correo'], 'Tu acceso al panel de ' . APP_NAME, Mailer::plantilla('Bienvenido(a) al equipo', '<p>Hola ' . e($d['nombre']) . ', ya tienes acceso al panel de gestión.</p><p>Correo: <b>' . e($d['correo']) . '</b><br>Contraseña temporal: <b>' . e($pass) . '</b></p><p>Cámbiala al ingresar desde <i>Mi perfil</i>.</p>'));
        flash('success', 'Empleado creado. Contraseña temporal (se muestra solo ahora): ' . $pass . ' — también se la enviamos por correo.'); redirect('admin/empleados');
    }
    public function actualizar(string $id): void
    {
        if (!Usuario::empleado((int) $id)) abort(404, 'Empleado no encontrado.');
        $d = $this->datos((int) $id); Usuario::actualizarEmpleado((int) $id, $d['documento'], $d['nombre'], $d['correo'], $d['telefono']);
        flash('success', 'Datos actualizados.'); redirect('admin/empleados');
    }
    public function estado(string $id): void
    {
        if (!Usuario::empleado((int) $id)) abort(404, 'Empleado no encontrado.');
        Usuario::cambiarEstado((int) $id, $this->post('estado') === 'activo' ? 'activo' : 'inactivo');
        flash('success', 'Estado actualizado. Un empleado inactivo no puede ingresar.'); redirect('admin/empleados');
    }
    public function clave(string $id): void
    {
        $e = Usuario::empleado((int) $id); if (!$e) abort(404, 'Empleado no encontrado.');
        $pass = Usuario::passwordTemporal(); Usuario::cambiarPassword((int) $id, $pass);
        Mailer::send($e['correo'], 'Nueva contraseña temporal · ' . APP_NAME, Mailer::plantilla('Restablecimos tu contraseña', '<p>Tu contraseña temporal es: <b>' . e($pass) . '</b></p><p>Cámbiala al ingresar desde <i>Mi perfil</i>.</p>'));
        flash('success', 'Nueva contraseña temporal para ' . $e['nombre'] . ' (se muestra solo ahora): ' . $pass); redirect('admin/empleados');
    }
}
