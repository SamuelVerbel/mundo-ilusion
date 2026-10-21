<?php
// Sesiones seguras: cookie HttpOnly, SameSite, expiración por inactividad (RNF05: 30 min).
class Session
{
    const INACTIVIDAD = 1800;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_name('mi_sesion');
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
        if (isset($_SESSION['_last']) && time() - $_SESSION['_last'] > self::INACTIVIDAD) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['_flash'] = [['warning', 'Tu sesión expiró por inactividad. Inicia sesión de nuevo.']];
        }
        $_SESSION['_last'] = time();
    }
    public static function flash(string $type, string $msg): void { $_SESSION['_flash'][] = [$type, $msg]; }
    public static function pullFlash(): array { $f = $_SESSION['_flash'] ?? []; unset($_SESSION['_flash']); return $f; }
    public static function withOld(array $data): void { unset($data['password'], $data['password_confirm'], $data['_token']); $_SESSION['_old'] = $data; }
    public static function clearOld(): void { unset($_SESSION['_old']); }
}
