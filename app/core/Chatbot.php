<?php
// Asistente virtual "Ilu". Dos modos:
//  1) Con IA: si CHATBOT_ENABLED=true y hay CHATBOT_API_KEY, consulta un modelo (API compatible con OpenAI: Groq, OpenAI, OpenRouter…)
//     dándole SOLO información real de la tienda (catálogo, precios, políticas) para que no invente.
//  2) Por reglas: sin clave, o si la IA falla / se agota el cupo diario, responde con palabras clave y datos reales de la base de datos.
// La clave vive solo en el servidor (.env): nunca llega al navegador.
class Chatbot
{
    const MAX_MENSAJE = 500;

    public static function enabled(): bool { return filter_var(env('CHATBOT_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN); }
    public static function conIA(): bool { return (string) env('CHATBOT_API_KEY', '') !== ''; }

    /** @return array{texto:string,modo:string} */
    public static function responder(string $msg, array $hist = []): array
    {
        $msg = trim(mb_substr(preg_replace('/\s+/u', ' ', $msg), 0, self::MAX_MENSAJE));
        if ($msg === '') return ['texto' => 'Escríbeme tu pregunta y con gusto te ayudo 😊', 'modo' => 'reglas'];
        if (self::conIA() && self::cupoIA()) {
            try {
                $t = self::llm($msg, $hist);
                if ($t !== '') return ['texto' => $t, 'modo' => 'ia'];
            } catch (Throwable $e) { error_log('[Chatbot] ' . $e->getMessage()); }   // si la IA falla, seguimos con reglas
        }
        return ['texto' => self::basico($msg), 'modo' => 'reglas'];
    }

    // ------------------------------------------------------------------ IA
    /** Tope diario de llamadas a la IA (CHATBOT_DAILY_LIMIT) para no gastar la cuota gratuita. */
    private static function cupoIA(): bool
    {
        $max = (int) env('CHATBOT_DAILY_LIMIT', 400); if ($max <= 0) return true;
        $dir = ROOT_PATH . '/storage/cache'; if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $f = $dir . '/chatbot_cupo.json'; $hoy = date('Y-m-d');
        $fh = @fopen($f, 'c+'); if (!$fh) return true;
        flock($fh, LOCK_EX);
        $d = json_decode((string) stream_get_contents($fh), true);
        if (!is_array($d) || ($d['dia'] ?? '') !== $hoy) $d = ['dia' => $hoy, 'n' => 0];
        $ok = $d['n'] < $max; if ($ok) $d['n']++;
        ftruncate($fh, 0); rewind($fh); fwrite($fh, json_encode($d)); flock($fh, LOCK_UN); fclose($fh);
        return $ok;
    }

    private static function llm(string $msg, array $hist): string
    {
        $messages = [['role' => 'system', 'content' => self::prompt()]];
        foreach (array_slice($hist, -8) as $h) {
            if (!is_array($h) || !in_array($h['role'] ?? '', ['user', 'assistant'], true)) continue;
            $messages[] = ['role' => $h['role'], 'content' => mb_substr((string) ($h['content'] ?? ''), 0, 800)];
        }
        $messages[] = ['role' => 'user', 'content' => $msg];
        $payload = json_encode([
            'model' => (string) env('CHATBOT_MODEL', 'llama-3.3-70b-versatile'),
            'messages' => $messages, 'temperature' => 0.4, 'max_tokens' => (int) env('CHATBOT_MAX_TOKENS', 400),
        ], JSON_UNESCAPED_UNICODE);
        $url = (string) env('CHATBOT_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
        $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . env('CHATBOT_API_KEY')];
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 6]);
            $res = curl_exec($ch); $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
        } else {
            $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $payload, 'timeout' => 15, 'ignore_errors' => true]]);
            $res = @file_get_contents($url, false, $ctx); $err = ''; $code = 0;
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) $code = (int) $m[1];
        }
        if ($res === false || $code < 200 || $code >= 300) throw new RuntimeException("API HTTP $code $err " . substr((string) $res, 0, 200));
        $j = json_decode((string) $res, true);
        return trim((string) ($j['choices'][0]['message']['content'] ?? ''));
    }

    private static function prompt(): string
    {
        $cat = Database::all("SELECT p.nombre, p.precio, p.disponible, p.id_producto, p.descripcion FROM producto p WHERE p.estado = 'activo' AND " . Producto::NO_ADICIONAL . ' ORDER BY p.nombre LIMIT 60');
        $lineas = [];
        foreach ($cat as $p) {
            $lineas[] = '- ' . $p['nombre'] . ' · ' . ((float) $p['precio'] > 0 ? cop($p['precio']) : 'precio por cotizar') . ($p['disponible'] ? '' : ' (agotado)')
                . ' · ' . '/producto/' . $p['id_producto'] . ' · ' . mb_substr(resumen_menu($p['descripcion'], 4), 0, 120);
        }
        $extras = array_map(fn($p) => $p['nombre'] . ' ' . cop($p['precio']), Producto::adicionales());
        $promos = array_map(fn($p) => $p['nombre'] . ' (' . (int) $p['porcentaje_descuento'] . '% hasta ' . fecha($p['fecha_fin']) . ')', Promocion::vigentes());
        $wa = conf('tienda_whatsapp');
        return "Eres Ilu, la asistente virtual de la tienda de regalos \"" . APP_NAME . "\" en Cartagena (Colombia). Hablas en español, con tono cálido, breve (máximo 4 frases) y claro. "
            . "Ayudas con: productos y precios, cómo hacer un pedido, pagos, entregas, horarios, promociones y PQR.\n"
            . "REGLAS: usa SOLO la información de abajo; si no la sabes, dilo y sugiere escribir por WhatsApp" . ($wa ? " ($wa)" : '') . " o usar la sección PQR. No inventes precios, descuentos, tiempos, zonas de cobertura ni políticas: si preguntan por un barrio o municipio concreto, di que la cobertura y el costo del domicilio se confirman por WhatsApp o en la política de entrega. "
            . "No pidas ni aceptes contraseñas, datos de tarjeta ni documentos. No hables de temas ajenos a la tienda. No prometas estados de pedidos: indica que los ve en 'Mis pedidos'. "
            . "Para recomendar un producto incluye su enlace en formato [nombre](ruta) usando la ruta que empieza por /. Puedes usar **negrita**.\n\n"
            . "TIENDA: " . conf('tienda_mensaje') . "\nUbicación: " . conf('tienda_direccion') . "\nHorarios: " . (conf('tienda_horarios') ?: 'no configurados') . "\n"
            . "Pedidos con mínimo " . (int) conf('anticipacion_horas', 24) . " horas de anticipación. Modalidades: domicilio o recogida en tienda. Pago en línea con Wompi (tarjeta, Nequi, PSE). "
            . "El cliente puede cancelar mientras el pedido esté pendiente de pago o recién recibido. Los pedidos sin pagar vencen a las " . (int) conf('expiracion_pedido_horas', 24) . " horas. "
            . "Todos los detalles son personalizados: la temática se cuenta en las observaciones al finalizar el pedido. " . conf('aviso_licor') . "\n"
            . 'Política de entrega: ' . conf('politica_entrega') . "\n"
            . "PROMOCIONES VIGENTES: " . ($promos ? implode('; ', $promos) : 'ninguna por ahora') . "\n"
            . "ADICIONALES (se agregan a cualquier detalle): " . implode('; ', $extras) . "\n"
            . "CATÁLOGO (nombre · precio · enlace · contenido):\n" . implode("\n", $lineas);
    }

    // ------------------------------------------------------------------ Reglas
    private static function e($v): string { return (string) $v; }   // la salida se escapa en el navegador; aquí no
    private static function n(string $t): string
    {
        $t = mb_strtolower($t);
        return strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }
    private static function tiene(string $t, array $palabras): bool
    {
        foreach ($palabras as $w) if (preg_match('/\b' . $w . '/u', $t)) return true;
        return false;
    }
    private static function wa(): string
    {
        $u = whatsapp_url('Hola Mundo Ilusión, quiero más información'); return $u ? "\n\nTambién puedes escribirnos por [WhatsApp]($u)." : '';
    }

    public static function basico(string $original): string
    {
        $t = self::n($original);
        if (self::tiene($t, ['hola', 'buenas', 'buenos dias', 'buen dia', 'buenas tardes', 'buenas noches', 'hey', 'saludos'])) {
            return "¡Hola! 💖 Soy **Ilu**, la asistente de " . APP_NAME . ". Puedo contarte sobre **productos y precios**, **cómo pedir**, **pagos**, **entregas**, **horarios** y **promociones**. ¿Qué detalle quieres regalar?";
        }
        if (self::tiene($t, ['gracias', 'muchas gracias', 'perfecto', 'listo ya', 'excelente'])) return '¡Con mucho gusto! 🎁 Si necesitas algo más, aquí estaré.';
        if (self::tiene($t, ['adios', 'chao', 'hasta luego', 'bye'])) return '¡Hasta pronto! Que tengas un día lleno de detalles bonitos ✨';

        if (self::tiene($t, ['promo', 'descuento', 'oferta', 'rebaja'])) {
            $p = Promocion::vigentes();
            if (!$p) return 'Por ahora no tenemos promociones vigentes, pero puedes ver las novedades en [Promociones](/promociones) 🎉';
            $l = array_map(fn($x) => '• **' . self::e($x['nombre']) . '**: ' . (int) $x['porcentaje_descuento'] . '% de descuento hasta el ' . fecha($x['fecha_fin']), $p);
            return "¡Sí hay promociones! 🔥\n" . implode("\n", $l) . "\n\nMíralas en [Promociones](/promociones).";
        }
        if (self::tiene($t, ['extra', 'adicional', 'agregar', 'globo extra', 'rosa', 'cerveza', 'peluche']) && !self::tiene($t, ['pedido'])) {
            $e = Producto::adicionales();
            if ($e) return "Puedes agregar detalles extra a cualquier regalo ✨\n" . implode("\n", array_map(fn($p) => '• ' . self::e($p['nombre']) . ': ' . cop($p['precio']), array_slice($e, 0, 6))) . "\n\nLos eliges en la página de cada producto." . (self::tiene($t, ['cerveza', 'licor', 'alcohol']) ? "\n\n" . self::e(conf('aviso_licor')) : '');
        }
        if (self::tiene($t, ['horario', 'hora de atencion', 'abren', 'abierto', 'cierran', 'atienden'])) {
            return conf('tienda_horarios') ? 'Nuestro horario de atención es: **' . self::e(conf('tienda_horarios')) . '**.' : 'Aún no tengo el horario cargado 😅. Escríbenos y te confirmamos.' . self::wa();
        }
        if (self::tiene($t, ['donde', 'ubicacion', 'direccion', 'quedan', 'estan ubicados', 'local', 'tienda fisica', 'recoger', 'recogida'])) {
            $d = conf('tienda_direccion');
            return 'Estamos en **' . self::e($d ?: 'Cartagena de Indias') . "**. Puedes elegir *recogida en tienda* o *domicilio* al finalizar tu pedido. Más datos en [La tienda](/tienda)." . self::wa();
        }
        if (self::tiene($t, ['domicilio', 'envio', 'enviar', 'entrega', 'entregan', 'llevan', 'cuanto demora', 'cuando llega'])) {
            return "Tenemos **domicilio** y **recogida en tienda**. Tú eliges la fecha y hora de entrega al finalizar el pedido; necesitamos mínimo **" . (int) conf('anticipacion_horas', 24) . " horas** de anticipación. Si nadie recibe, nos comunicamos contigo para reprogramar." . self::wa();
        }
        if (self::tiene($t, ['pago', 'pagar', 'tarjeta', 'nequi', 'pse', 'transferencia', 'efectivo', 'wompi', 'daviplata'])) {
            return "El pago es en línea con **Wompi**: tarjeta, **Nequi** o **PSE**. Es seguro: nosotros no guardamos los datos de tu tarjeta 🔒. Tu pedido se confirma apenas Wompi aprueba el pago, y si no pagas se cancela solo a las " . (int) conf('expiracion_pedido_horas', 24) . " horas.";
        }
        if (self::tiene($t, ['cancelar', 'devolucion', 'devolver', 'reembolso', 'reclamo', 'queja', 'sugerencia', 'peticion', 'pqr'])) {
            return "Puedes cancelar tu pedido desde [Mis pedidos](/pedidos) mientras esté *pendiente de pago* o *recibido*. Para quejas, reclamos o sugerencias usa el formulario de [PQR](/pqr): te respondemos por correo." . self::wa();
        }
        if (self::tiene($t, ['mi pedido', 'estado del pedido', 'donde va', 'rastrear', 'seguimiento', 'mis pedidos'])) {
            return 'Puedes ver el estado de tus pedidos en [Mis pedidos](/pedidos) (debes iniciar sesión). Cada cambio de estado también llega a tu cuenta 📦';
        }
        if (self::tiene($t, ['como pido', 'como hago', 'como realizo', 'hacer pedido', 'como pedir', 'como compro', 'como comprar', 'como hacer', 'pasos', 'quiero pedir', 'hacer un pedido', 'registr', 'cuenta'])) {
            return "Es muy fácil 💕\n1. Elige tu detalle en el [catálogo](/catalogo)\n2. Agrégalo al carrito (necesitas [crear una cuenta](/registro))\n3. Elige fecha, *domicilio o recogida* y cuéntanos tu temática en observaciones\n4. Paga con Wompi y ¡listo!";
        }
        if (self::tiene($t, ['personaliz', 'tematica', 'a mi gusto', 'dedicatoria', 'foto', 'cambiar', 'modificar'])) {
            return self::e(conf('tienda_nota_personalizacion')) . " Cuéntanos tu temática en las **observaciones** al finalizar el pedido 🎨";
        }
        if (self::tiene($t, ['licor', 'alcohol', 'menor de edad', 'mayor de edad'])) return self::e(conf('aviso_licor'));
        if (self::tiene($t, ['contacto', 'telefono', 'whatsapp', 'instagram', 'correo', 'comunicarme', 'hablar con'])) {
            $x = []; if (conf('tienda_telefono')) $x[] = 'Teléfono: **' . self::e(conf('tienda_telefono')) . '**'; if (conf('tienda_instagram')) $x[] = 'Instagram: **' . self::e(conf('tienda_instagram')) . '**';
            return ($x ? implode("\n", $x) : 'Escríbenos para ayudarte personalmente') . self::wa();
        }
        if (self::tiene($t, ['categoria', 'ocasion', 'que venden', 'que tienen', 'que ofrecen', 'productos', 'catalogo', 'opciones'])) {
            $c = Categoria::activas();
            if ($c) return "Tenemos:\n" . implode("\n", array_map(fn($x) => '• [' . self::e($x['nombre']) . '](/catalogo?categoria=' . (int) $x['id_categoria'] . ') (' . (int) $x['total'] . ')', $c)) . "\n\nO mira todo el [catálogo](/catalogo) 🎁";
        }

        // ¿Pregunta por un producto concreto o por precios?
        $stop = ['cuanto', 'cuesta', 'precio', 'precios', 'valor', 'tienen', 'quiero', 'busco', 'necesito', 'para', 'como', 'algo', 'regalo', 'regalos', 'detalle', 'detalles', 'unos', 'unas', 'favor', 'saber', 'sobre', 'cual', 'cuales', 'del', 'los', 'las', 'una', 'que', 'con', 'por'];
        $palabras = array_values(array_filter(preg_split('/[^a-z0-9]+/', $t), fn($w) => strlen($w) >= 4 && !in_array($w, $stop, true)));
        $quierePrecio = self::tiene($t, ['cuanto', 'precio', 'valor', 'cuesta', 'barato', 'economic']);
        if ($palabras) {
            $or = implode(' OR ', array_fill(0, count($palabras), '(p.nombre LIKE ? OR p.descripcion LIKE ? OR EXISTS (SELECT 1 FROM producto_categoria qc JOIN categoria qa ON qa.id_categoria = qc.id_categoria WHERE qc.id_producto = p.id_producto AND qa.nombre LIKE ?))')); $par = [];
            foreach ($palabras as $w) { $par[] = "%$w%"; $par[] = "%$w%"; $par[] = "%$w%"; }
            // puntaje: coincidencia en el nombre (3) + en la categoría (2) + en la descripción (1)
            $score = implode(' + ', array_fill(0, count($palabras), '((p.nombre LIKE ?) * 3 + EXISTS (SELECT 1 FROM producto_categoria qc JOIN categoria qa ON qa.id_categoria = qc.id_categoria WHERE qc.id_producto = p.id_producto AND qa.nombre LIKE ?) * 2 + (p.descripcion LIKE ?))')); $ps = [];
            foreach ($palabras as $w) { $ps[] = "%$w%"; $ps[] = "%$w%"; $ps[] = "%$w%"; }
            $r = Database::all(str_replace('FROM producto p', ', (' . $score . ') AS puntaje FROM producto p', Producto::BASE) . " WHERE p.estado = 'activo' AND " . Producto::NO_ADICIONAL . " AND ($or) ORDER BY puntaje DESC, p.nombre LIMIT 4", array_merge($ps, $par));
            if ($r) {
                $l = array_map(function ($p) {
                    $f = Producto::precioFinal((float) $p['precio'], (float) $p['descuento']);
                    return '• [' . self::e($p['nombre']) . '](/producto/' . (int) $p['id_producto'] . ') — ' . ((float) $p['precio'] > 0 ? cop($f) . ($p['descuento'] > 0 ? ' (con ' . (int) $p['descuento'] . '% de descuento)' : '') : 'precio por cotizar');
                }, $r);
                return "Mira lo que encontré 💝\n" . implode("\n", $l) . "\n\nTodos se personalizan a tu gusto.";
            }
        }
        if ($quierePrecio) {
            $m = Database::one('SELECT MIN(precio) mn, MAX(precio) mx FROM producto p WHERE p.estado = \'activo\' AND precio > 0 AND ' . Producto::NO_ADICIONAL);
            if ($m && $m['mn']) return 'Nuestros detalles van desde **' . cop($m['mn']) . '** hasta **' . cop($m['mx']) . "**, según el diseño y los adicionales. Dime qué tipo de regalo buscas o mira el [catálogo](/catalogo) 🎁";
        }
        return "No estoy segura de haber entendido 😅. Puedo ayudarte con **productos y precios**, **cómo pedir**, **pagos**, **entregas**, **horarios** y **promociones**. Prueba preguntando, por ejemplo: *«¿cuánto cuesta un desayuno sorpresa?»*." . self::wa();
    }
}
