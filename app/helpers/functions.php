<?php
// Funciones auxiliares usadas en vistas y controladores.

function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }          // protege contra XSS
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function asset(string $path): string { return url($path); }
function absolute_url(string $path = ''): string {
    $base = env('APP_URL');
    if (!$base) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL;
    }
    return rtrim((string) $base, '/') . '/' . ltrim($path, '/');
}
function cop($n): string { return '$' . number_format((float) $n, 0, ',', '.'); }
function fecha($d, bool $hora = false): string {
    if (!$d) return '—';
    $t = strtotime((string) $d);
    return date($hora ? 'd/m/Y H:i' : 'd/m/Y', $t);
}
function old(string $key, $default = '') { return $_SESSION['_old'][$key] ?? $default; }
function csrf_field(): string { return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">'; }
function redirect(string $path): never { header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path))); exit; }
function abort(int $code, string $msg = ''): never {
    http_response_code($code);
    (new Controller())->renderError($code, $msg);
    exit;
}
function flash(string $type, string $msg): void { Session::flash($type, $msg); }
function conf(string $key, $default = '') { return Configuracion::get($key, $default); }
function img_producto(?string $ruta): string {
    if (!$ruta) return asset('img/sin-imagen.svg');
    return asset((str_starts_with($ruta, 'img/') ? '' : 'uploads/productos/') . $ruta);
}
const ESTADOS_PEDIDO = [
    'pendiente_pago' => ['Pendiente de pago', 'warning'], 'recibido' => ['Recibido', 'info'],
    'confirmado' => ['Confirmado', 'primary'], 'en_preparacion' => ['En preparación', 'primary'],
    'en_camino' => ['En camino', 'primary'], 'listo_para_recoger' => ['Listo para recoger', 'success'],
    'entregado' => ['Entregado', 'success'], 'cancelado' => ['Cancelado', 'danger'],
];
function badge_pedido(string $estado): string {
    [$txt, $c] = ESTADOS_PEDIDO[$estado] ?? [$estado, 'secondary'];
    return '<span class="badge text-bg-' . $c . '">' . e($txt) . '</span>';
}
function badge_pago(?string $estado): string {
    $m = ['pendiente' => 'warning', 'aprobado' => 'success', 'rechazado' => 'danger', 'error' => 'danger', 'anulado' => 'secondary', 'reembolsado' => 'dark'];
    return $estado ? '<span class="badge text-bg-' . ($m[$estado] ?? 'secondary') . '">' . e(ucfirst($estado)) . '</span>' : '<span class="text-muted">—</span>';
}

/** Convierte la descripción estructurada ("Diseño:" / "- punto") en HTML seguro con títulos y listas. */
function descripcion_html(?string $t): string
{
    $out = ''; $abierta = false;
    foreach (preg_split('/\R/', (string) $t) as $l) {
        $l = trim($l); if ($l === '') continue;
        if (str_starts_with($l, '- ')) { if (!$abierta) { $out .= '<ul class="desc-lista">'; $abierta = true; } $out .= '<li>' . e(substr($l, 2)) . '</li>'; continue; }
        if ($abierta) { $out .= '</ul>'; $abierta = false; }
        $out .= str_ends_with($l, ':') ? '<h6 class="desc-titulo">' . e(rtrim($l, ':')) . '</h6>' : '<p>' . e($l) . '</p>';
    }
    return $out . ($abierta ? '</ul>' : '');
}
/** Resumen corto para las tarjetas: los primeros elementos del "Menú:" sin cantidades ni alternativas. */
function resumen_menu(?string $t, int $n = 4): string
{
    if (!preg_match('/Menú:\R((?:- .*\R?)+)/u', (string) $t, $m)) return '';
    $items = [];
    foreach (preg_split('/\R/', trim($m[1])) as $x) {
        $x = trim(substr(trim($x), 2));
        $x = preg_replace('/^\d+\s+/u', '', $x);                 // quita "1 "
        $x = preg_split('/,| o /u', $x)[0];                        // "Sándwich, waffles o tamal" -> "Sándwich"
        $items[] = mb_strtoupper(mb_substr($x, 0, 1)) . mb_substr($x, 1);
    }
    return implode(' · ', array_slice($items, 0, $n));
}
function contiene_licor(?string $t): bool { return (bool) preg_match('/cerveza|coronita/i', (string) $t); }
/** Enlace de WhatsApp a partir del número guardado en configuración (agrega 57 si son 10 dígitos). */
function whatsapp_url(string $mensaje = ''): string
{
    $n = preg_replace('/\D/', '', (string) conf('tienda_whatsapp')); if ($n === '') return '';
    if (strlen($n) === 10) $n = '57' . $n;
    return 'https://wa.me/' . $n . ($mensaje !== '' ? '?text=' . rawurlencode($mensaje) : '');
}
