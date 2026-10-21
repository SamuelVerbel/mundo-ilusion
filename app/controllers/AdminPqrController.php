<?php
class AdminPqrController extends AdminController
{
    public function index(): void
    {
        $estado = $_GET['estado'] ?? ''; $tipo = $_GET['tipo'] ?? '';
        $this->adminView('admin/pqr/index', ['title' => 'PQR de clientes', 'items' => Pqr::listar($estado, $tipo), 'estado' => $estado, 'tipo' => $tipo]);
    }
    public function show(string $id): void
    {
        $q = Pqr::find((int) $id); if (!$q) abort(404, 'Mensaje no encontrado.');
        $this->adminView('admin/pqr/show', ['title' => 'PQR #' . (int) $q['id_pqr'], 'q' => $q]);
    }
    public function responder(string $id): void
    {
        $r = $this->post('respuesta');
        $v = (new Validator())->required('respuesta', $r, 'La respuesta')->min('respuesta', $r, 5, 'La respuesta')->max('respuesta', $r, 2000, 'La respuesta');
        if ($v->fails()) $this->back($v->errors, "admin/pqr/$id");
        if (!Pqr::responder((int) $id, $r, Auth::id())) { flash('danger', 'Este mensaje no se puede responder (no existe o ya está cerrado).'); redirect('admin/pqr'); }
        flash('success', 'Respuesta enviada al cliente por correo.'); redirect('admin/pqr');
    }
    public function cerrar(string $id): void { Pqr::cerrar((int) $id); flash('success', 'Caso cerrado.'); redirect('admin/pqr'); }
}
