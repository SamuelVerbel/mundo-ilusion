<?php
class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', ['title' => 'Mundo Ilusión · Tienda de regalos', 'destacados' => Producto::destacados(8),
            'categorias' => Categoria::activas(), 'promos' => array_slice(Promocion::vigentes(), 0, 1)]);
    }
}
