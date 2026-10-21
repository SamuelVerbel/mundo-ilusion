<?php
class Direccion
{
    public static function deUsuario(int $uid): array { return Database::all("SELECT * FROM direccion WHERE id_usuario = ? AND estado = 'activo' ORDER BY id_direccion DESC", [$uid]); }
    public static function find(int $id, int $uid): ?array { return Database::one("SELECT * FROM direccion WHERE id_direccion = ? AND id_usuario = ? AND estado = 'activo'", [$id, $uid]); }
    public static function crear(int $uid, string $dir, string $barrio, ?string $ref): int
    {
        return Database::insert('INSERT INTO direccion (id_usuario, direccion, barrio, referencia) VALUES (?,?,?,?)', [$uid, $dir, $barrio, $ref ?: null]);
    }
    /** Una dirección usada en pedidos no se edita ni se borra: se desactiva (los pedidos históricos no cambian). */
    public static function desactivar(int $id, int $uid): void { Database::exec("UPDATE direccion SET estado = 'inactivo' WHERE id_direccion = ? AND id_usuario = ?", [$id, $uid]); }
}
