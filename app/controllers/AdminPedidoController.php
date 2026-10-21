<?php
class AdminPedidoController extends AdminController
{
    public function index(): void
    {
        Pedido::expirarVencidos();
        $f = ['estado' => $_GET['estado'] ?? '', 'desde' => $_GET['desde'] ?? '', 'hasta' => $_GET['hasta'] ?? '', 'q' => trim($_GET['q'] ?? '')];
        $this->adminView('admin/pedidos/index', ['title' => 'Pedidos', 'f' => $f, 'r' => Pedido::listar($f, (int) ($_GET['page'] ?? 1))]);
    }
    public function show(string $id): void
    {
        $p = Pedido::find((int) $id); if (!$p) abort(404, 'Pedido no encontrado.');
        $this->adminView('admin/pedidos/show', ['title' => 'Pedido #' . $p['id_pedido'], 'p' => $p, 'detalles' => Pedido::detalles((int) $id), 'historial' => Pedido::historialDe((int) $id),
            'pagos' => Pedido::pagos((int) $id), 'siguientes' => Pedido::siguientes($p, Auth::role())]);
    }
    public function estado(string $id): void
    {
        $err = Pedido::cambiarEstado((int) $id, $this->post('estado'), Auth::id(), Auth::role(), $this->post('nota') ?: null);
        flash($err ? 'danger' : 'success', $err ?: 'Estado actualizado.');
        redirect('admin/pedidos/' . (int) $id);
    }
    public function devolucion(string $id): void
    {
        $err = Pago::reembolsar((int) $id, Auth::id());
        flash($err ? 'danger' : 'success', $err ?: 'Devolución registrada.');
        redirect('admin/pedidos/' . (int) $id);
    }
}
