<?php
// Pantalla "Configuración" del panel: el administrador edita los datos de la tienda y los parámetros sin tocar la base de datos.
class AdminConfigController extends AdminController
{
    const GRUPOS = [
        'Parámetros de pedidos' => ['anticipacion_horas', 'expiracion_pedido_horas'],
        'Datos de la tienda' => ['tienda_nombre', 'tienda_slogan', 'tienda_mensaje', 'tienda_nota_personalizacion', 'tienda_direccion', 'tienda_horarios', 'tienda_telefono', 'tienda_whatsapp', 'tienda_instagram', 'tienda_facebook', 'correo_negocio'],
        'Avisos y políticas' => ['aviso_licor', 'politica_entrega', 'politica_cambios_devoluciones', 'politica_datos'],
    ];
    public function index(): void
    {
        $filas = []; foreach (Database::all('SELECT clave, valor, descripcion FROM configuracion') as $r) $filas[$r['clave']] = $r;
        $this->adminView('admin/config/index', ['title' => 'Configuración de la tienda', 'grupos' => self::GRUPOS, 'filas' => $filas]);
    }
    public function guardar(): void
    {
        $validas = array_merge(...array_values(self::GRUPOS)); $v = new Validator(); $datos = [];
        foreach ($validas as $k) $datos[$k] = $this->post($k);
        foreach (['anticipacion_horas', 'expiracion_pedido_horas'] as $k) $v->regex($k, $datos[$k], '/^\d{1,4}$/', 'Los parámetros de horas deben ser números enteros.');
        if ($datos['correo_negocio'] !== '') $v->email('correo_negocio', $datos['correo_negocio'], 'El correo del negocio');
        if ($datos['tienda_whatsapp'] !== '') $v->regex('tienda_whatsapp', $datos['tienda_whatsapp'], '/^\+?[\d\s]{7,15}$/', 'El WhatsApp debe tener solo números.');
        if ($v->fails()) $this->back($v->errors, 'admin/configuracion');
        foreach ($datos as $k => $val) if (mb_strlen($val) <= 1000) Configuracion::set($k, $val);
        flash('success', 'Configuración guardada.');
        redirect('admin/configuracion');
    }
}
