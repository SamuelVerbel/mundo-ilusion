<?php
class AdminCategoriaController extends AdminController
{
    public function index(): void { $this->adminView('admin/categorias/index', ['title' => 'Categorías y ocasiones', 'cats' => Categoria::todas()]); }
    public function nuevo(): void { $this->adminView('admin/categorias/form', ['title' => 'Nueva categoría', 'c' => null]); }
    public function editar(string $id): void
    {
        $c = Categoria::find((int) $id); if (!$c) abort(404, 'Categoría no encontrada.');
        $this->adminView('admin/categorias/form', ['title' => 'Editar categoría', 'c' => $c]);
    }
    public function crear(): void { $this->guardar(null); }
    public function actualizar(string $id): void { $this->guardar((int) $id); }
    private function guardar(?int $id): void
    {
        $d = ['nombre' => $this->post('nombre'), 'descripcion' => $this->post('descripcion'), 'tipo' => $this->post('tipo')];
        $back = $id ? "admin/categorias/$id/editar" : 'admin/categorias/nuevo';
        $v = (new Validator())->required('nombre', $d['nombre'], 'El nombre')->max('nombre', $d['nombre'], 80, 'El nombre')->max('descripcion', $d['descripcion'], 255, 'La descripción')
            ->in('tipo', $d['tipo'], ['categoria', 'ocasion', 'adicional'], 'Selecciona el tipo.');
        if (!$v->fails()) $v->custom('nombre', !Categoria::nombreTomado($d['nombre'], (int) $id), 'Ya existe una categoría con ese nombre.');
        if ($v->fails()) $this->back($v->errors, $back);
        Categoria::guardar($id, $d);
        flash('success', 'Categoría guardada.'); redirect('admin/categorias');
    }
    public function estado(string $id): void { Categoria::cambiarEstado((int) $id, $this->post('estado') === 'activo' ? 'activo' : 'inactivo'); flash('success', 'Estado actualizado.'); redirect('admin/categorias'); }
    public function eliminar(string $id): void
    {
        if (Categoria::productosAsociados((int) $id) > 0) { flash('danger', 'Esta categoría tiene productos asociados. Reasígnalos o desactívala.'); redirect('admin/categorias'); }
        Categoria::eliminar((int) $id); flash('success', 'Categoría eliminada.'); redirect('admin/categorias');
    }
}
