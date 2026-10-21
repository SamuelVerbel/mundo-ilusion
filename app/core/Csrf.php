<?php
// Protección CSRF: cada formulario POST lleva un token secreto que se valida en el servidor.
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf'];
    }
    public static function verify(?string $t): bool { return is_string($t) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $t); }
}
