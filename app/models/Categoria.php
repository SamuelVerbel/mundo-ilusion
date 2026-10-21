<?php
class Categoria
{
    /** Categorías visibles en la tienda (sin los "adicionales"), con la foto del primer producto de cada una. */
    public static function activas(): array
    {
        return Database::all("SELECT c.*, (SELECT i.ruta FROM producto_categoria pc JOIN producto p ON p.id_producto = pc.id_producto AND p.estado = 'activo'
                JOIN producto_imagen i ON i.id_producto = p.id_producto AND i.es_principal = 1 WHERE pc.id_categoria = c.id_categoria ORDER BY p.id_producto LIMIT 1) AS imagen,
                (SELECT COUNT(*) FROM producto_categoria pc JOIN producto p ON p.id_producto = pc.id_producto AND p.estado = 'activo' WHERE pc.id_categoria = c.id_categoria) AS total
            FROM categoria c WHERE c.estado = 'activo' AND c.tipo <> 'adicional' ORDER BY FIELD(c.tipo,'categoria','ocasion'), c.id_categoria");
    }
    /** Todas, incluidas las de tipo adicional (para el formulario de productos del panel). */
    public static function paraAdmin(): array { return Database::all("SELECT * FROM categoria WHERE estado = 'activo' ORDER BY FIELD(tipo,'categoria','ocasion','adicional'), nombre"); }
    public static function todas(): array { return Database::all('SELECT c.*, (SELECT COUNT(*) FROM producto_categoria pc WHERE pc.id_categoria = c.id_categoria) AS productos FROM categoria c ORDER BY tipo, nombre'); }
    public static function find(int $id): ?array { return Database::one('SELECT * FROM categoria WHERE id_categoria = ?', [$id]); }
    public static function nombreTomado(string $n, int $excepto = 0): bool { return (bool) Database::val('SELECT 1 FROM categoria WHERE nombre = ? AND id_categoria <> ?', [$n, $excepto]); }
    public static function guardar(?int $id, array $d): int
    {
        if ($id) { Database::exec('UPDATE categoria SET nombre=?, descripcion=?, tipo=? WHERE id_categoria=?', [$d['nombre'], $d['descripcion'] ?: null, $d['tipo'], $id]); return $id; }
        return Database::insert('INSERT INTO categoria (nombre, descripcion, tipo) VALUES (?,?,?)', [$d['nombre'], $d['descripcion'] ?: null, $d['tipo']]);
    }
    public static function cambiarEstado(int $id, string $estado): void { Database::exec('UPDATE categoria SET estado = ? WHERE id_categoria = ?', [$estado, $id]); }
    public static function productosAsociados(int $id): int { return (int) Database::val('SELECT COUNT(*) FROM producto_categoria WHERE id_categoria = ?', [$id]); }
    public static function eliminar(int $id): void { Database::exec('DELETE FROM categoria WHERE id_categoria = ?', [$id]); }
}
