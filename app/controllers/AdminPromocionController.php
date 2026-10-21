<?php
class AdminPromocionController extends AdminController
{
    public function index(): void { $this->adminView('admin/promociones/index', ['title' => 'Promociones', 'promos' => Promocion::todas()]); }
    public function nuevo(): void { $this->adminView('admin/promociones/form', ['title' => 'Nueva promoción', 'p' => null, 'productos' => Promocion::productosParaElegir()]); }
    public function editar(string $id): void
    {
        $p = Promocion::find((int) $id); if (!$p) abort(404, 'Promoción no encontrada.');
        $this->adminView('admin/promociones/form', ['title' => 'Editar promoción', 'p' => $p, 'productos' => Promocion::productosParaElegir()]);
    }
    public function crear(): void { $this->guardar(null); }
    public function actualizar(string $id): void { $this->guardar((int) $id); }
    private function guardar(?int $id): void
    {
        $d = ['nombre' => $this->post('nombre'), 'porcentaje' => $this->post('porcentaje'), 'inicio' => $this->post('inicio'), 'fin' => $this->post('fin'), 'estado' => $this->post('estado') === 'inactiva' ? 'inactiva' : 'activa'];
        $prods = array_filter((array) ($_POST['productos'] ?? []), 'is_scalar');
        $back = $id ? "admin/promociones/$id/editar" : 'admin/promociones/nuevo';
        $f = fn($x) => ($t = DateTime::createFromFormat('Y-m-d', $x)) && $t->format('Y-m-d') === $x;
        $v = (new Validator())->required('nombre', $d['nombre'], 'El nombre')->max('nombre', $d['nombre'], 120, 'El nombre')
            ->custom('porcentaje', is_numeric($d['porcentaje']) && $d['porcentaje'] >= 1 && $d['porcentaje'] <= 100, 'El descuento debe estar entre 1% y 100%.')
            ->custom('inicio', $f($d['inicio']), 'La fecha de inicio no es válida.')->custom('fin', $f($d['fin']), 'La fecha de fin no es válida.');
        if (!$v->fails()) $v->custom('fin', $d['fin'] >= $d['inicio'], 'La fecha de fin no puede ser anterior a la de inicio.');
        if (!$v->fails()) $v->custom('productos', count($prods) > 0, 'Selecciona al menos un producto.');
        if ($v->fails()) $this->back($v->errors, $back);
        // solo productos que realmente existen y están activos
        $validos = array_column(Promocion::productosParaElegir(), 'id_producto');
        $prods = array_values(array_intersect(array_map('intval', $prods), array_map('intval', $validos)));
        if (!$prods) $this->back(['productos' => 'Selecciona al menos un producto válido.'], $back);
        Promocion::guardar($id, $d, $prods);
        flash('success', 'Promoción guardada. ' . ($d['estado'] === 'activa' ? 'Los precios con descuento se ven en el catálogo mientras esté vigente.' : 'Está inactiva: no se aplica.')); redirect('admin/promociones');
    }
    public function estado(string $id): void { Promocion::cambiarEstado((int) $id, $this->post('estado') === 'activa' ? 'activa' : 'inactiva'); flash('success', 'Estado actualizado.'); redirect('admin/promociones'); }
    public function eliminar(string $id): void { Promocion::eliminar((int) $id); flash('success', 'Promoción eliminada. Los pedidos ya hechos conservan su precio.'); redirect('admin/promociones'); }
}
