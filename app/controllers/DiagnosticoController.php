<?php
// Página de diagnóstico: comprueba que PHP, la conexión a MySQL y las carpetas estén bien.
// SOLO funciona con APP_ENV=local (en producción devuelve 404) y nunca muestra contraseñas.
class DiagnosticoController extends Controller
{
    const TABLAS = ['usuario', 'direccion', 'password_reset', 'categoria', 'producto', 'producto_imagen', 'producto_categoria', 'promocion', 'producto_promocion',
        'carrito', 'detalle_carrito', 'pedido', 'detalle_pedido', 'pedido_estado_historial', 'pago', 'evento_pago', 'pqr', 'configuracion'];

    public function index(): void
    {
        if (APP_ENV !== 'local') abort(404, 'La página que buscas no existe.');
        $r = [];   // [grupo, descripción, ok(true/false/null=aviso), detalle]
        $add = function (string $g, string $d, ?bool $ok, string $det = '') use (&$r) { $r[] = [$g, $d, $ok, $det]; };

        // 1. PHP
        $add('PHP', 'Versión de PHP 8.1 o superior', version_compare(PHP_VERSION, '8.1.0', '>='), 'Tienes ' . PHP_VERSION);
        foreach (['pdo_mysql', 'mbstring', 'curl', 'fileinfo', 'openssl'] as $ext) $add('PHP', "Extensión $ext", extension_loaded($ext), extension_loaded($ext) ? 'activa' : 'ACTIVAR en php.ini (quitar el ; delante de extension=' . $ext . ')');

        // 2. Configuración
        $add('Configuración', 'Archivo .env existe', is_file(ROOT_PATH . '/.env'), is_file(ROOT_PATH . '/.env') ? '' : 'Copia .env.example como .env');
        $add('Configuración', 'APP_ENV', null, APP_ENV . (APP_ENV === 'local' ? ' (correcto para desarrollo)' : ''));
        $add('Configuración', 'Dirección base detectada (BASE_URL)', null, BASE_URL === '' ? '(raíz)' : BASE_URL);
        $add('Configuración', 'Wompi configurado', Wompi::configurado() ? true : null, Wompi::configurado() ? 'llaves presentes (' . env('WOMPI_ENV', 'sandbox') . ')' : 'sin llaves todavía (usa "Simular pago")');
        $add('Configuración', 'Correo', null, filter_var(env('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN) ? 'SMTP activado' : 'modo desarrollo: los correos se guardan en storage/mail/');

        // 3. Base de datos
        $db = null;
        try {
            $db = Database::pdo();
            $add('Base de datos', 'Conexión a MySQL (' . env('DB_HOST', '127.0.0.1') . ':' . env('DB_PORT', '3306') . ', usuario ' . env('DB_USER', 'root') . ')', true, 'versión ' . $db->query('SELECT VERSION()')->fetchColumn());
            $add('Base de datos', 'Base "' . env('DB_NAME', 'mundo_ilusion') . '" seleccionada', true, '');
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            $pista = str_contains($msg, 'Unknown database') ? 'Falta importar database/schema.sql en phpMyAdmin.' : (str_contains($msg, 'Access denied') ? 'Revisa DB_USER y DB_PASS en .env.' : (str_contains($msg, 'refused') || str_contains($msg, 'No such file') ? 'MySQL no está encendido: pulsa Start en XAMPP.' : ''));
            $add('Base de datos', 'Conexión a MySQL', false, $pista ?: 'Error de conexión (revisa .env)');
        }
        if ($db) {
            $existentes = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $faltan = array_values(array_diff(self::TABLAS, $existentes));
            $add('Base de datos', 'Las 18 tablas del modelo', !$faltan, $faltan ? 'Faltan: ' . implode(', ', $faltan) : count(self::TABLAS) . ' de 18 encontradas');
            if (!$faltan) {
                foreach (['usuario' => 3, 'producto' => 1, 'categoria' => 1, 'configuracion' => 1] as $t => $min) {
                    $n = (int) $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                    $add('Datos de demostración', "Registros en $t", $n >= $min, "$n registro(s)" . ($n < $min ? ' — falta importar database/seed.sql' : ''));
                }
                $add('Datos de demostración', 'Pedidos registrados', null, (int) $db->query('SELECT COUNT(*) FROM pedido')->fetchColumn() . ' (normal que sea 0 al inicio)');
            }
        }

        // 4. Carpetas con permiso de escritura
        foreach (['storage/mail', 'storage/logs', 'public/uploads/productos'] as $d) {
            $ok = is_dir(ROOT_PATH . "/$d") && is_writable(ROOT_PATH . "/$d");
            $add('Carpetas', "$d se puede escribir", $ok, $ok ? '' : 'Crea la carpeta o dale permiso de escritura');
        }

        $this->pintar($r);
    }

    private function pintar(array $r): void
    {
        $fallas = count(array_filter($r, fn($x) => $x[2] === false));
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Diagnóstico</title>
<style>body{font-family:system-ui,Arial,sans-serif;background:#FFF8FB;color:#2B2530;margin:0;padding:24px}main{max-width:860px;margin:auto}h1{color:#E0457B;margin:0 0 4px}
.banner{padding:14px 18px;border-radius:12px;margin:16px 0;font-weight:700}.ok{background:#e3f6e8;color:#1c6b36}.mal{background:#fde2e2;color:#9b1c1c}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06)}td,th{padding:9px 12px;border-bottom:1px solid #f1e6ec;text-align:left;font-size:14px}th{background:#E0457B;color:#fff}
.g{background:#FCE4EE;font-weight:700}.s{font-weight:700}.s1{color:#1c9b46}.s0{color:#c0262d}.s2{color:#b8860b}small{color:#777}</style></head><body><main>
<h1>✿ Diagnóstico de Mundo Ilusión</h1><small>Solo visible con APP_ENV=local. No muestra contraseñas.</small>';
        echo $fallas ? '<div class="banner mal">❌ Hay ' . $fallas . ' problema(s) por corregir (marcados en rojo).</div>' : '<div class="banner ok">✅ Todo está bien: PHP, la base de datos y las carpetas funcionan.</div>';
        echo '<table><tr><th style="width:34px"></th><th>Comprobación</th><th>Resultado</th></tr>';
        $g = '';
        foreach ($r as [$grupo, $desc, $ok, $det]) {
            if ($grupo !== $g) { $g = $grupo; echo '<tr><td colspan="3" class="g">' . e($g) . '</td></tr>'; }
            $ic = $ok === true ? '<span class="s s1">✔</span>' : ($ok === false ? '<span class="s s0">✘</span>' : '<span class="s s2">i</span>');
            echo '<tr><td>' . $ic . '</td><td>' . e($desc) . '</td><td>' . e($det) . '</td></tr>';
        }
        echo '</table><p><a href="' . e(url()) . '">← Volver a la tienda</a></p></main></body></html>';
    }
}
