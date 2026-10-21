<?php
// Controlador base: dibuja vistas dentro de un layout y ofrece utilidades.
class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        ob_start();
        require ROOT_PATH . "/app/views/$view.php";
        $content = ob_get_clean();
        require ROOT_PATH . "/app/views/layouts/$layout.php";
        Session::clearOld();
    }
    public function renderError(int $code, string $msg): void
    {
        $this->view('errors/error', ['title' => "Error $code", 'code' => $code, 'msg' => $msg]);
    }
    /** Ruta (con query) de la página anterior, solo si es de este mismo sitio. */
    protected function refererPath(): ?string
    {
        $r = $_SERVER['HTTP_REFERER'] ?? ''; if (!$r) return null;
        $u = parse_url($r); if (($u['host'] ?? '') !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) return null;
        return ($u['path'] ?? '/') . (isset($u['query']) ? '?' . $u['query'] : '');
    }
    protected function post(string $k, $d = ''): string { return trim((string) ($_POST[$k] ?? $d)); }
    protected function back(array $errors = [], ?string $fallback = null): never
    {
        Session::withOld($_POST);
        if ($errors) flash('danger', implode(' ', array_values($errors)));
        redirect($fallback ?? ltrim(substr($_SERVER['HTTP_REFERER'] ?? '', strlen(absolute_url(''))), '/'));
    }
}
