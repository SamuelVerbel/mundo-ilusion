<?php
// Conexión única a MySQL con PDO + atajos para consultas con parámetros (previene inyección SQL).
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'), env('DB_PORT', '3306'), env('DB_NAME', 'mundo_ilusion'));
            self::$pdo = new PDO($dsn, (string) env('DB_USER', 'root'), (string) env('DB_PASS', ''), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::$pdo->exec("SET time_zone = '-05:00'");
        }
        return self::$pdo;
    }
    public static function run(string $sql, array $p = []): PDOStatement { $s = self::pdo()->prepare($sql); $s->execute($p); return $s; }
    public static function all(string $sql, array $p = []): array { return self::run($sql, $p)->fetchAll(); }
    public static function one(string $sql, array $p = []): ?array { $r = self::run($sql, $p)->fetch(); return $r ?: null; }
    public static function val(string $sql, array $p = []) { $r = self::run($sql, $p)->fetchColumn(); return $r === false ? null : $r; }
    public static function exec(string $sql, array $p = []): int { return self::run($sql, $p)->rowCount(); }
    public static function insert(string $sql, array $p = []): int { self::run($sql, $p); return (int) self::pdo()->lastInsertId(); }
    public static function begin(): void { self::pdo()->beginTransaction(); }
    public static function commit(): void { if (self::pdo()->inTransaction()) self::pdo()->commit(); }
    public static function rollback(): void { if (self::pdo()->inTransaction()) self::pdo()->rollBack(); }
}
