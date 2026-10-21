<?php
// Autenticación y roles: cliente, empleado, administrador.
class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = $_SESSION['uid'] ?? null;
            if ($id) {
                $u = Usuario::find((int) $id);
                if ($u && $u['estado'] === 'activo') self::$user = $u; else { unset($_SESSION['uid']); }
            }
        }
        return self::$user;
    }
    public static function check(): bool { return self::user() !== null; }
    public static function id(): ?int { return self::user()['id_usuario'] ?? null; }
    public static function role(): ?string { return self::user()['rol'] ?? null; }
    public static function is(string ...$roles): bool { return in_array(self::role(), $roles, true); }
    public static function isStaff(): bool { return self::is('empleado', 'administrador'); }

    public static function login(array $u): void
    {
        session_regenerate_id(true);       // evita fijación de sesión
        $_SESSION['uid'] = (int) $u['id_usuario'];
        self::$user = $u; self::$loaded = true;
        Usuario::registrarAcceso((int) $u['id_usuario']);
    }
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']); }
        session_destroy();
        self::$user = null; self::$loaded = false;
    }
    /** Home según rol */
    public static function home(): string { return self::isStaff() ? 'admin' : ''; }
}
