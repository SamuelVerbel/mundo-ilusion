<?php
class Promocion
{
    public static function vigentes(): array
    {
        $promos = Database::all("SELECT * FROM promocion WHERE estado = 'activa' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin ORDER BY porcentaje_descuento DESC");
        foreach ($promos as &$p) {
            $rows = Database::all(Producto::BASE . " JOIN producto_promocion x ON x.id_producto = p.id_producto WHERE x.id_promocion = ? AND p.estado = 'activo' ORDER BY p.nombre", [$p['id_promocion']]);
            $p['productos'] = array_map(function ($r) { $r['precio_final'] = Producto::precioFinal((float) $r['precio'], (float) $r['descuento']); $r['con_descuento'] = (float) $r['descuento'] > 0; return $r; }, $rows);
        }
        return $promos;
    }

    public static function todas(): array
    {
        return Database::all("SELECT pr.*, (SELECT COUNT(*) FROM producto_promocion x WHERE x.id_promocion = pr.id_promocion) AS productos,
            (pr.estado = 'activa' AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin) AS vigente FROM promocion pr ORDER BY vigente DESC, pr.fecha_fin DESC");
    }
    public static function find(int $id): ?array
    {
        $p = Database::one('SELECT * FROM promocion WHERE id_promocion = ?', [$id]); if (!$p) return null;
        $p['ids'] = array_map('intval', array_column(Database::all('SELECT id_producto FROM producto_promocion WHERE id_promocion = ?', [$id]), 'id_producto'));
        return $p;
    }
    /** Productos que se pueden poner en promoción (activos, sin los extras). */
    public static function productosParaElegir(): array
    {
        return Database::all("SELECT p.id_producto, p.nombre, p.precio FROM producto p WHERE p.estado = 'activo' AND " . Producto::NO_ADICIONAL . ' ORDER BY p.nombre');
    }
    public static function guardar(?int $id, array $d, array $productos): int
    {
        Database::begin();
        try {
            if ($id) Database::exec('UPDATE promocion SET nombre=?, porcentaje_descuento=?, fecha_inicio=?, fecha_fin=?, estado=? WHERE id_promocion=?', [$d['nombre'], $d['porcentaje'], $d['inicio'], $d['fin'], $d['estado'], $id]);
            else $id = Database::insert('INSERT INTO promocion (nombre, porcentaje_descuento, fecha_inicio, fecha_fin, estado) VALUES (?,?,?,?,?)', [$d['nombre'], $d['porcentaje'], $d['inicio'], $d['fin'], $d['estado']]);
            Database::exec('DELETE FROM producto_promocion WHERE id_promocion = ?', [$id]);
            foreach (array_unique(array_map('intval', $productos)) as $pid) Database::exec('INSERT INTO producto_promocion (id_producto, id_promocion) VALUES (?,?)', [$pid, $id]);
            Database::commit();
        } catch (Throwable $e) { Database::rollback(); throw $e; }
        return $id;
    }
    public static function cambiarEstado(int $id, string $estado): void { Database::exec('UPDATE promocion SET estado = ? WHERE id_promocion = ?', [$estado, $id]); }
    public static function eliminar(int $id): void { Database::exec('DELETE FROM promocion WHERE id_promocion = ?', [$id]); }
}
