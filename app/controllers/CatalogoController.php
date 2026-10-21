<?php
class CatalogoController extends Controller
{
    public function index(): void
    {
        $f = ['q' => trim($_GET['q'] ?? ''), 'categoria' => (int) ($_GET['categoria'] ?? 0), 'orden' => $_GET['orden'] ?? '', 'promo' => !empty($_GET['promo'])];
        $r = Producto::buscar($f, (int) ($_GET['page'] ?? 1));
        $this->view('catalogo/index', ['title' => 'Catálogo · Mundo Ilusión', 'f' => $f, 'r' => $r, 'categorias' => Categoria::activas()]);
    }
    public function show(string $id): void
    {
        $p = Producto::find((int) $id);
        if (!$p) abort(404, 'Este producto no existe o ya no está disponible.');
        $this->view('catalogo/show', ['title' => $p['nombre'], 'p' => $p, 'relacionados' => Producto::relacionados($p), 'extras' => Producto::adicionales()]);
    }
    public function promociones(): void
    {
        $this->view('catalogo/promociones', ['title' => 'Promociones · Mundo Ilusión', 'promos' => Promocion::vigentes()]);
    }
}
