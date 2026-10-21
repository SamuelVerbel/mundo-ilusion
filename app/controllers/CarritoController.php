<?php
class CarritoController extends Controller
{
    public function index(): void
    {
        $items = Carrito::items(Auth::id());
        $this->view('carrito/index', ['title' => 'Mi carrito', 'items' => $items, 'r' => Carrito::resumen($items), 'extras' => Producto::adicionales()]);
    }
    public function agregar(): void
    {
        $id = (int) ($_POST['id_producto'] ?? 0); $cant = (int) ($_POST['cantidad'] ?? 1);
        $volver = $this->refererPath();
        if (!Auth::check()) { $_SESSION['intended'] = $volver ?: (BASE_URL . '/catalogo'); flash('warning', 'Inicia sesión o crea tu cuenta para agregar productos al carrito.'); redirect('login'); }
        if (!Auth::is('cliente')) { flash('warning', 'Solo los clientes pueden usar el carrito.'); redirect(''); }
        $err = Carrito::agregar(Auth::id(), $id, $cant);
        flash($err ? 'danger' : 'success', $err ?: 'Producto agregado al carrito.');
        if (!$err && !empty($_POST['ir_carrito'])) redirect('carrito');
        if ($volver) { header('Location: ' . $volver); exit; }
        redirect('catalogo');
    }
    public function actualizar(): void
    {
        $err = Carrito::actualizar(Auth::id(), (int) ($_POST['id_producto'] ?? 0), (int) ($_POST['cantidad'] ?? 0));
        if ($err) flash('danger', $err);
        redirect('carrito');
    }
    public function eliminar(): void { Carrito::eliminar(Auth::id(), (int) ($_POST['id_producto'] ?? 0)); flash('success', 'Producto eliminado del carrito.'); redirect('carrito'); }
}
