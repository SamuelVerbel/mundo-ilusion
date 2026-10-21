<?php
class AdminReporteController extends AdminController
{
    private function rango(): array
    {
        $f = fn($x) => ($t = DateTime::createFromFormat('Y-m-d', (string) $x)) && $t->format('Y-m-d') === $x;
        $d = $_GET['desde'] ?? ''; $h = $_GET['hasta'] ?? '';
        if (!$f($d)) $d = date('Y-m-01');
        if (!$f($h)) $h = date('Y-m-d');
        if ($h < $d) [$d, $h] = [$h, $d];
        return [$d, $h];
    }
    public function index(): void
    {
        [$d, $h] = $this->rango();
        $this->adminView('admin/reportes/index', ['title' => 'Reportes de ventas', 'desde' => $d, 'hasta' => $h, 'r' => ReporteVentas::generar($d, $h)]);
    }
    public function csv(): void { [$d, $h] = $this->rango(); ReporteVentas::descargarCsv($d, $h); }
}
