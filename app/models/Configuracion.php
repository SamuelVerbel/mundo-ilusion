<?php
// Parámetros e información de la tienda (tabla configuracion).
class Configuracion
{
    private static ?array $cache = null;
    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try { foreach (Database::all('SELECT clave, valor FROM configuracion') as $r) self::$cache[$r['clave']] = $r['valor']; } catch (Throwable $e) {}
        }
        return self::$cache;
    }
    public static function get(string $k, $d = '') { return self::all()[$k] ?? $d; }
    public static function set(string $k, string $v): void
    {
        Database::exec('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)', [$k, $v]);
        self::$cache = null;
    }
}
