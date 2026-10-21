<?php
// Reportes de ventas. Cuentan solo pedidos ya pagados: se excluyen "pendiente_pago" y "cancelado".
class ReporteVentas
{
    const FILTRO = "p.estado NOT IN ('pendiente_pago','cancelado') AND DATE(p.fecha_pedido) BETWEEN ? AND ?";

    public static function generar(string $desde, string $hasta): array
    {
        $par = [$desde, $hasta];
        $r = Database::one('SELECT COUNT(*) AS pedidos, COALESCE(SUM(p.total),0) AS ventas, COALESCE(SUM(p.descuento_total),0) AS descuentos FROM pedido p WHERE ' . self::FILTRO, $par);
        $r['ticket'] = $r['pedidos'] ? round($r['ventas'] / $r['pedidos']) : 0;
        $r['por_dia'] = Database::all('SELECT DATE(p.fecha_pedido) AS dia, COUNT(*) AS pedidos, SUM(p.total) AS ventas FROM pedido p WHERE ' . self::FILTRO . ' GROUP BY dia ORDER BY dia', $par);
        $r['por_modalidad'] = Database::all('SELECT p.modalidad_entrega AS modalidad, COUNT(*) AS pedidos, SUM(p.total) AS ventas FROM pedido p WHERE ' . self::FILTRO . ' GROUP BY p.modalidad_entrega', $par);
        $r['por_estado'] = Database::all('SELECT p.estado, COUNT(*) AS pedidos FROM pedido p WHERE ' . self::FILTRO . ' GROUP BY p.estado ORDER BY pedidos DESC', $par);
        $r['top'] = Database::all('SELECT d.nombre_producto AS producto, SUM(d.cantidad) AS unidades, SUM(d.subtotal) AS ventas FROM detalle_pedido d JOIN pedido p ON p.id_pedido = d.id_pedido
            WHERE ' . self::FILTRO . ' GROUP BY d.id_producto, d.nombre_producto ORDER BY unidades DESC, ventas DESC LIMIT 10', $par);
        $r['canceladas'] = (int) Database::val("SELECT COUNT(*) FROM pedido p WHERE p.estado = 'cancelado' AND DATE(p.fecha_pedido) BETWEEN ? AND ?", $par);
        return $r;
    }
    public static function filas(string $desde, string $hasta): array
    {
        return Database::all('SELECT p.id_pedido, p.fecha_pedido, u.nombre AS cliente, p.modalidad_entrega, p.fecha_entrega, p.estado, p.subtotal, p.descuento_total, p.total
            FROM pedido p JOIN usuario u ON u.id_usuario = p.id_usuario WHERE ' . self::FILTRO . ' ORDER BY p.id_pedido', [$desde, $hasta]);
    }
    /** CSV para Excel: BOM UTF-8, separador ";" y protección contra inyección de fórmulas (=, +, -, @). */
    public static function descargarCsv(string $desde, string $hasta): never
    {
        $cel = function ($v): string {
            $v = (string) $v; if ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) $v = "'" . $v;
            return '"' . str_replace('"', '""', $v) . '"';
        };
        header('Content-Type: text/csv; charset=utf-8'); header("Content-Disposition: attachment; filename=\"ventas_{$desde}_a_{$hasta}.csv\"");
        echo "\xEF\xBB\xBF" . implode(';', array_map($cel, ['Pedido', 'Fecha del pedido', 'Cliente', 'Modalidad', 'Fecha de entrega', 'Estado', 'Subtotal', 'Descuento', 'Total'])) . "\r\n";
        foreach (self::filas($desde, $hasta) as $f) echo implode(';', array_map($cel, [$f['id_pedido'], $f['fecha_pedido'], $f['cliente'], $f['modalidad_entrega'], $f['fecha_entrega'], $f['estado'], (int) $f['subtotal'], (int) $f['descuento_total'], (int) $f['total']])) . "\r\n";
        exit;
    }
}
