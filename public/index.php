<?php
declare(strict_types=1);
// Único punto de entrada (front controller). Todo pasa por aquí.
require dirname(__DIR__) . '/config/config.php';

spl_autoload_register(function (string $class) {
    foreach (['core', 'models', 'controllers'] as $dir) {
        $f = ROOT_PATH . "/app/$dir/$class.php";
        if (is_file($f)) { require $f; return; }
    }
});

Session::start();

set_exception_handler(function (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    $msg = APP_ENV === 'local' ? $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')' : 'Ocurrió un error inesperado. Inténtalo de nuevo.';
    try { (new Controller())->renderError(500, $msg); } catch (Throwable $x) { echo 'Error 500'; }
});

$router = new Router();
require ROOT_PATH . '/routes/web.php';
$router->dispatch();
