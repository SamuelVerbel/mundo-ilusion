<?php
class AdminProductoController extends AdminController
{
    public function index(): void
    {
        $f = ['q' => trim($_GET['q'] ?? '')];
        $this->adminView('admin/productos/index', ['title' => 'Productos', 'f' => $f, 'r' => Producto::buscar($f, (int) ($_GET['page'] ?? 1), 15, true)]);
    }
    public function nuevo(): void { $this->adminView('admin/productos/form', ['title' => 'Nuevo producto', 'p' => null, 'categorias' => Categoria::paraAdmin(), 'sel' => []]); }
    public function editar(string $id): void
    {
        $p = Producto::find((int) $id, false); if (!$p) abort(404, 'Producto no encontrado.');
        $this->adminView('admin/productos/form', ['title' => 'Editar producto', 'p' => $p, 'categorias' => Categoria::paraAdmin(), 'sel' => array_column($p['categorias'], 'id_categoria')]);
    }
    public function crear(): void { $this->guardar(null); }
    public function actualizar(string $id): void { $this->guardar((int) $id); }

    private function guardar(?int $id): void
    {
        $d = ['nombre' => $this->post('nombre'), 'descripcion' => $this->post('descripcion'), 'precio' => str_replace(['.', ','], ['', '.'], $this->post('precio')), 'disponible' => empty($_POST['disponible']) ? 0 : 1];
        $back = $id ? "admin/productos/$id/editar" : 'admin/productos/nuevo';
        $cats = array_map('intval', (array) ($_POST['categorias'] ?? []));
        $v = (new Validator())->required('nombre', $d['nombre'], 'El nombre')->max('nombre', $d['nombre'], 150, 'El nombre')
            ->required('precio', $d['precio'], 'El precio')->custom('precio', is_numeric($d['precio']) && (float) $d['precio'] > 0 && (float) $d['precio'] < 100000000, 'El precio debe ser un número mayor a cero.')
            ->custom('categorias', count($cats) > 0, 'Selecciona al menos una categoría u ocasión.');
        if ($v->fails()) $this->back($v->errors, $back);
        $archivos = Upload::lista('imagenes'); $subidas = [];
        try { foreach ($archivos as $f) $subidas[] = Upload::imagen($f); }
        catch (RuntimeException $e) { foreach ($subidas as $s) Upload::borrar($s); $this->back(['imagen' => $e->getMessage()], $back); }
        $id = Producto::guardar($id, $d, $cats);
        foreach ($subidas as $s) Producto::agregarImagen($id, $s);
        flash('success', 'Producto guardado.');
        redirect('admin/productos');
    }
    public function estado(string $id): void
    {
        Producto::cambiarEstado((int) $id, $this->post('estado') === 'activo' ? 'activo' : 'inactivo');
        flash('success', 'Estado del producto actualizado.'); redirect('admin/productos');
    }
    public function eliminar(string $id): void
    {
        if (Producto::usadoEnPedidos((int) $id)) { flash('danger', 'Este producto ya estuvo en pedidos, no se puede eliminar. Puedes desactivarlo.'); redirect('admin/productos'); }
        foreach (Producto::eliminar((int) $id) as $r) Upload::borrar($r);
        flash('success', 'Producto eliminado.'); redirect('admin/productos');
    }
    public function imagenEliminar(string $id, string $img): void
    {
        $im = Producto::imagen((int) $img);
        if ($im && (int) $im['id_producto'] === (int) $id) { Producto::eliminarImagen((int) $img); Upload::borrar($im['ruta']); }
        redirect("admin/productos/$id/editar");
    }
    public function imagenPrincipal(string $id, string $img): void
    {
        $im = Producto::imagen((int) $img);
        if ($im && (int) $im['id_producto'] === (int) $id) Producto::hacerPrincipal((int) $img);
        redirect("admin/productos/$id/editar");
    }
}
