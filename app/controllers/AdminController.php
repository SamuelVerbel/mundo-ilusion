<?php
class AdminController extends Controller
{
    protected function adminView(string $view, array $data = []): void { $this->view($view, $data, 'admin'); }
    public function dashboard(): void
    {
        Pedido::expirarVencidos();
        $this->adminView('admin/dashboard', ['title' => 'Panel', 's' => Pedido::estadisticas(), 'recientes' => Pedido::listar([], 1, 8)['items']]);
    }
}
