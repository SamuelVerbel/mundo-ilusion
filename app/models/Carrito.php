<?php
class Carrito
{
    /** Devuelve el id del carrito activo del cliente (lo crea si no existe). */
    public static function activo(int $uid): int
    {
        $id = Database::val("SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = 'activo' ORDER BY id_carrito DESC LIMIT 1", [$uid]);
        return $id ? (int) $id : Database::insert('INSERT INTO carrito (id_usuario) VALUES (?)', [$uid]);
    }
    public static function items(int $uid): array
    {
        // reutiliza la consulta base de productos (imagen, descuento vigente) y le suma la cantidad del carrito
        $sql = str_replace('SELECT p.*,', 'SELECT p.*, d.cantidad, d.id_detalle_carrito,', Producto::BASE)
             . " JOIN detalle_carrito d ON d.id_producto = p.id_producto JOIN carrito c ON c.id_carrito = d.id_carrito AND c.estado = 'activo'
                 WHERE c.id_usuario = ? ORDER BY d.id_detalle_carrito";
        $items = Database::all($sql, [$uid]);
        foreach ($items as &$i) {
            $i['precio_final'] = Producto::precioFinal((float) $i['precio'], (float) $i['descuento']);
            $i['subtotal'] = $i['precio_final'] * (int) $i['cantidad'];
            $i['ahorro'] = ((float) $i['precio'] - $i['precio_final']) * (int) $i['cantidad'];
            $i['comprable'] = $i['estado'] === 'activo' && (int) $i['disponible'] === 1;
        }
        return $items;
    }
    public static function resumen(array $items): array
    {
        $sub = 0; $desc = 0; $bad = 0;
        foreach ($items as $i) { $sub += (float) $i['precio'] * (int) $i['cantidad']; $desc += $i['ahorro']; if (!$i['comprable']) $bad++; }
        return ['subtotal' => $sub, 'descuento' => $desc, 'total' => $sub - $desc, 'no_disponibles' => $bad, 'cantidad' => array_sum(array_column($items, 'cantidad'))];
    }
    public static function contar(int $uid): int
    {
        return (int) Database::val("SELECT COALESCE(SUM(d.cantidad),0) FROM detalle_carrito d JOIN carrito c ON c.id_carrito = d.id_carrito AND c.estado = 'activo' WHERE c.id_usuario = ?", [$uid]);
    }
    /** Devuelve null si todo salió bien, o un mensaje de error. */
    public static function agregar(int $uid, int $idProducto, int $cant): ?string
    {
        $p = Database::one('SELECT estado, disponible FROM producto WHERE id_producto = ?', [$idProducto]);
        if (!$p || $p['estado'] !== 'activo') return 'El producto ya no existe.';
        if (!(int) $p['disponible']) return 'Este producto no está disponible por ahora.';
        $cant = max(1, min(99, $cant));
        Database::exec('INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad) VALUES (?,?,?) ON DUPLICATE KEY UPDATE cantidad = LEAST(cantidad + VALUES(cantidad), 99)', [self::activo($uid), $idProducto, $cant]);
        return null;
    }
    public static function actualizar(int $uid, int $idProducto, int $cant): ?string
    {
        if ($cant < 1) return 'La cantidad debe ser mayor a cero. Para quitar el producto usa "Eliminar".';
        Database::exec('UPDATE detalle_carrito SET cantidad = ? WHERE id_producto = ? AND id_carrito = ?', [min($cant, 99), $idProducto, self::activo($uid)]);
        return null;
    }
    public static function eliminar(int $uid, int $idProducto): void { Database::exec('DELETE FROM detalle_carrito WHERE id_producto = ? AND id_carrito = ?', [$idProducto, self::activo($uid)]); }
}
