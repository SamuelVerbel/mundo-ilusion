<?php
// Enrutador: asocia URL + método (GET/POST) con un controlador y valida CSRF y roles.
class Router
{
    private array $routes = [];

    public function get(string $p, array $h, array $o = []): void  { $this->add('GET', $p, $h, $o); }
    public function post(string $p, array $h, array $o = []): void { $this->add('POST', $p, $h, $o); }
    private function add(string $m, string $p, array $h, array $o): void
    {
        $re = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $p) . '$#';
        $this->routes[$m][] = [$re, $h, $o];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] === 'HEAD' ? 'GET' : $_SERVER['REQUEST_METHOD'];
        $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
        foreach ([BASE_URL, dirname(BASE_URL)] as $base) {
            if ($base !== '' && $base !== '/' && $base !== '\\' && str_starts_with($path, $base . '/') || $path === $base) { $path = substr($path, strlen($base)); break; }
        }
        $path = '/' . trim($path, '/');

        foreach ($this->routes[$method] ?? [] as [$re, [$class, $action], $opt]) {
            if (!preg_match($re, $path, $m)) continue;
            if ($method === 'POST' && ($opt['csrf'] ?? true) && !Csrf::verify($_POST['_token'] ?? null)) {
                abort(419, 'La página expiró. Vuelve atrás, recarga el formulario e inténtalo de nuevo.');
            }
            if (isset($opt['roles'])) {
                if (!Auth::check()) { $_SESSION['intended'] = $_SERVER['REQUEST_URI']; flash('warning', 'Inicia sesión para continuar.'); redirect('login'); }
                if (!Auth::is(...$opt['roles'])) abort(403, 'No tienes permisos para acceder a esta sección.');
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            (new $class())->$action(...array_values($params));
            return;
        }
        abort(404, 'La página que buscas no existe.');
    }
}
