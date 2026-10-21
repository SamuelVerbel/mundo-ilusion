<?php
class PasswordReset
{
    /** Crea un token de un solo uso (vence en 1 hora). Se guarda solo su hash. */
    public static function crear(int $uid): string
    {
        $token = bin2hex(random_bytes(32));
        Database::exec('UPDATE password_reset SET usado = 1 WHERE id_usuario = ? AND usado = 0', [$uid]);
        Database::insert('INSERT INTO password_reset (id_usuario, token_hash, expira_en) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))', [$uid, hash('sha256', $token)]);
        return $token;
    }
    public static function validar(string $token): ?array
    {
        return Database::one('SELECT * FROM password_reset WHERE token_hash = ? AND usado = 0 AND expira_en > NOW()', [hash('sha256', $token)]);
    }
    public static function marcarUsado(int $id): void { Database::exec('UPDATE password_reset SET usado = 1 WHERE id_reset = ?', [$id]); }
}
