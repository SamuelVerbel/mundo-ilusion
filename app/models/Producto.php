<?php
class Producto
{
    /** Columnas comunes: imagen principal, primera categoría y mayor descuento VIGENTE (promociones activas y dentro de fechas). */
    const BASE = "SELECT p.*,
        (SELECT i.ruta FROM producto_imagen i WHERE i.id_producto = p.id_producto ORDER BY i.es_principal DESC, i.orden, i.id_imagen LIMIT 1) AS imagen,
        (SELECT c.nombre FROM producto_categoria pc JOIN categoria c ON c.id_categoria = pc.id_categoria WHERE pc.id_producto = p.id_producto ORDER BY c.tipo, c.nombre LIMIT 1) AS categoria,
        COALESCE((SELECT MAX(pr.porcentaje_descuento) FROM producto_promocion pp JOIN promocion pr ON pr.id_promocion = pp.id_promocion
                  WHERE pp.id_producto = p.id_producto AND pr.estado = 'activa' AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin), 0) AS descuento
        FROM producto p";

    /** Condición SQL: el producto NO pertenece a una categoría de tipo "adicional". */
    const NO_ADICIONAL = "NOT EXISTS (SELECT 1 FROM producto_categoria xa JOIN categoria ca ON ca.id_categoria = xa.id_categoria WHERE xa.id_producto = p.id_producto AND ca.tipo = 'adicional')";

    public static function precioFinal(float $precio, float $descuento): float { return round($precio * (100 - $descuento) / 100, 0); }
    private static function enriquecer(array $p): array
    {
        $p['precio_final'] = self::precioFinal((float) $p['precio'], (float) $p['descuento']);
        $p['con_descuento'] = (float) $p['descuento'] > 0;
        return $p;
    }

    /** Catálogo público o de administración con búsqueda, filtro por categoría, orden y paginación. */
    public static function buscar(array $f = [], int $page = 1, int $per = 12, bool $admin = false): array
    {
        $where = []; $par = [];
        if (!$admin) { $where[] = "p.estado = 'activo'"; $where[] = self::NO_ADICIONAL; }   // los extras no salen en el catálogo
        if (!empty($f['q'])) { $where[] = 'p.nombre LIKE ?'; $par[] = '%' . $f['q'] . '%'; }
        if (!empty($f['categoria'])) { $where[] = 'EXISTS (SELECT 1 FROM producto_categoria x WHERE x.id_producto = p.id_producto AND x.id_categoria = ?)'; $par[] = (int) $f['categoria']; }
        if (!empty($f['promo'])) { $where[] = 'EXISTS (SELECT 1 FROM producto_promocion pp JOIN promocion pr ON pr.id_promocion = pp.id_promocion WHERE pp.id_producto = p.id_producto AND pr.estado = \'activa\' AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin)'; }
        $w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $orden = ['precio_asc' => 'p.precio ASC', 'precio_desc' => 'p.precio DESC', 'nuevos' => 'p.id_producto DESC'][$f['orden'] ?? ''] ?? 'p.nombre ASC';
        $total = (int) Database::val("SELECT COUNT(*) FROM producto p$w", $par);
        $pages = max(1, (int) ceil($total / $per));
        $page = min(max(1, $page), $pages);
        $off = ($page - 1) * $per;
        $items = array_map([self::class, 'enriquecer'], Database::all(self::BASE . "$w ORDER BY $orden LIMIT " . (int) $per . ' OFFSET ' . (int) $off, $par));
        return ['items' => $items, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }
    /** Productos para la portada: una mezcla que rota cada día (sin extras). */
    public static function destacados(int $n = 8): array
    {
        return array_map([self::class, 'enriquecer'], Database::all(self::BASE . " WHERE p.estado = 'activo' AND p.disponible = 1 AND " . self::NO_ADICIONAL . "
            ORDER BY descuento DESC, MOD(p.id_producto * (DAYOFYEAR(CURDATE()) + 7), 97) LIMIT " . (int) $n));
    }
    /** Extras (globos, rosas, cervezas…) que se pueden agregar a cualquier detalle. */
    public static function adicionales(): array
    {
        return array_map([self::class, 'enriquecer'], Database::all(self::BASE . " WHERE p.estado = 'activo' AND p.disponible = 1 AND NOT " . self::NO_ADICIONAL . " ORDER BY p.precio, p.nombre"));
    }
    public static function find(int $id, bool $soloActivos = true): ?array
    {
        $p = Database::one(self::BASE . ' WHERE p.id_producto = ?' . ($soloActivos ? " AND p.estado = 'activo'" : ''), [$id]);
        if (!$p) return null;
        $p = self::enriquecer($p);
        $p['imagenes'] = self::imagenes($id);
        $p['categorias'] = Database::all('SELECT c.* FROM producto_categoria pc JOIN categoria c ON c.id_categoria = pc.id_categoria WHERE pc.id_producto = ? ORDER BY c.tipo, c.nombre', [$id]);
        return $p;
    }
    public static function imagenes(int $id): array { return Database::all('SELECT * FROM producto_imagen WHERE id_producto = ? ORDER BY es_principal DESC, orden, id_imagen', [$id]); }
    public static function relacionados(array $p, int $n = 4): array
    {
        $ids = array_column($p['categorias'] ?? [], 'id_categoria');
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = Database::all(self::BASE . " WHERE p.estado = 'activo' AND " . self::NO_ADICIONAL . " AND p.id_producto <> ? AND EXISTS (SELECT 1 FROM producto_categoria x WHERE x.id_producto = p.id_producto AND x.id_categoria IN ($in)) ORDER BY p.id_producto DESC LIMIT " . (int) $n, array_merge([$p['id_producto']], $ids));
        return array_map([self::class, 'enriquecer'], $rows);
    }

    // ---------- administración ----------
    public static function guardar(?int $id, array $d, array $categorias): int
    {
        Database::begin();
        try {
            if ($id) Database::exec('UPDATE producto SET nombre=?, descripcion=?, precio=?, disponible=? WHERE id_producto=?', [$d['nombre'], $d['descripcion'] ?: null, $d['precio'], $d['disponible'], $id]);
            else $id = Database::insert('INSERT INTO producto (nombre, descripcion, precio, disponible) VALUES (?,?,?,?)', [$d['nombre'], $d['descripcion'] ?: null, $d['precio'], $d['disponible']]);
            Database::exec('DELETE FROM producto_categoria WHERE id_producto = ?', [$id]);
            foreach (array_unique(array_map('intval', $categorias)) as $c) Database::exec('INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?,?)', [$id, $c]);
            Database::commit();
        } catch (Throwable $e) { Database::rollback(); throw $e; }
        return $id;
    }
    public static function agregarImagen(int $id, string $ruta): void
    {
        $hay = (int) Database::val('SELECT COUNT(*) FROM producto_imagen WHERE id_producto = ?', [$id]);
        Database::insert('INSERT INTO producto_imagen (id_producto, ruta, orden, es_principal) VALUES (?,?,?,?)', [$id, $ruta, $hay, $hay === 0 ? 1 : 0]);
    }
    public static function imagen(int $idImg): ?array { return Database::one('SELECT * FROM producto_imagen WHERE id_imagen = ?', [$idImg]); }
    public static function eliminarImagen(int $idImg): void
    {
        $im = self::imagen($idImg); if (!$im) return;
        Database::exec('DELETE FROM producto_imagen WHERE id_imagen = ?', [$idImg]);
        if ($im['es_principal']) Database::exec('UPDATE producto_imagen SET es_principal = 1 WHERE id_producto = ? ORDER BY orden, id_imagen LIMIT 1', [$im['id_producto']]);
    }
    public static function hacerPrincipal(int $idImg): void
    {
        $im = self::imagen($idImg); if (!$im) return;
        Database::exec('UPDATE producto_imagen SET es_principal = 0 WHERE id_producto = ?', [$im['id_producto']]);
        Database::exec('UPDATE producto_imagen SET es_principal = 1 WHERE id_imagen = ?', [$idImg]);
    }
    public static function cambiarEstado(int $id, string $estado): void { Database::exec('UPDATE producto SET estado = ? WHERE id_producto = ?', [$estado, $id]); }
    public static function usadoEnPedidos(int $id): bool { return (bool) Database::val('SELECT 1 FROM detalle_pedido WHERE id_producto = ? LIMIT 1', [$id]); }
    public static function eliminar(int $id): array
    {
        $rutas = array_column(self::imagenes($id), 'ruta');
        Database::exec('DELETE FROM detalle_carrito WHERE id_producto = ?', [$id]);
        Database::exec('DELETE FROM producto WHERE id_producto = ?', [$id]);   // imágenes y relaciones caen en cascada
        return $rutas;
    }
}
