<?php
declare(strict_types=1);
// Configuración central. Lee el archivo .env una sola vez; nada de claves en otros archivos.
define('ROOT_PATH', dirname(__DIR__));

$_ENV = $_ENV ?? [];
$envFile = ROOT_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $v = trim($v);
        // quitar comentario al final "valor   # comentario" (si no va entre comillas)
        if ($v !== '' && $v[0] === '#') $v = '';                                   // "CLAVE=   # comentario" => vacío
        elseif ($v !== '' && $v[0] !== '"' && $v[0] !== "'") $v = trim(preg_split('/\s+#/', $v)[0]);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}
function env(string $key, $default = null) {
    $v = $_ENV[$key] ?? $default;
    return $v === '' ? $default : $v;
}

define('APP_ENV', (string) env('APP_ENV', 'local'));
define('APP_NAME', (string) env('APP_NAME', 'Mundo Ilusión'));
date_default_timezone_set('America/Bogota');
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'local' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/storage/logs/php-errors.log');

// Carpeta base de la URL (funciona en localhost:8000 y en localhost/mundo-ilusion/public)
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/'));

require ROOT_PATH . '/app/helpers/functions.php';
