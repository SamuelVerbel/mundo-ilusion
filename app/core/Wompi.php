<?php
// Integración con Wompi (Web Checkout). Documentación: https://docs.wompi.co
class Wompi
{
    public static function configurado(): bool { return env('WOMPI_PUBLIC_KEY') && env('WOMPI_INTEGRITY_SECRET'); }
    public static function apiBase(): string { return env('WOMPI_ENV', 'sandbox') === 'production' ? 'https://production.wompi.co/v1' : 'https://sandbox.wompi.co/v1'; }

    /** Firma de integridad = SHA256(referencia + monto_en_centavos + moneda + secreto_de_integridad) */
    public static function firma(string $ref, int $cents, string $moneda, string $secreto): string { return hash('sha256', $ref . $cents . $moneda . $secreto); }

    public static function checkoutUrl(array $pago, string $redirectUrl): string
    {
        $cents = (int) round($pago['monto'] * 100);
        return 'https://checkout.wompi.co/p/?' . http_build_query([
            'public-key' => env('WOMPI_PUBLIC_KEY'), 'currency' => 'COP', 'amount-in-cents' => $cents, 'reference' => $pago['referencia'],
            'signature:integrity' => self::firma($pago['referencia'], $cents, 'COP', (string) env('WOMPI_INTEGRITY_SECRET')),
            'redirect-url' => $redirectUrl,
        ], '', '&', PHP_QUERY_RFC3986);
    }
    /** Verifica el checksum de un evento: SHA256(valores de signature.properties en orden + timestamp + secreto_de_eventos) */
    public static function checksumEvento(array $evento, string $secreto): string
    {
        $concat = '';
        foreach ($evento['signature']['properties'] ?? [] as $path) {
            $v = $evento['data'] ?? [];
            foreach (explode('.', $path) as $k) $v = is_array($v) && array_key_exists($k, $v) ? $v[$k] : '';
            $concat .= is_scalar($v) ? (string) $v : '';
        }
        return hash('sha256', $concat . ($evento['timestamp'] ?? '') . $secreto);
    }
    public static function eventoValido(array $evento, string $secreto): bool
    {
        return $secreto !== '' && isset($evento['signature']['checksum']) && hash_equals(strtolower($evento['signature']['checksum']), self::checksumEvento($evento, $secreto));
    }
    /** Consulta una transacción directamente a Wompi (útil al volver del checkout, sobre todo en local sin webhook). */
    public static function consultarTransaccion(string $id): ?array
    {
        if (!function_exists('curl_init')) return null;
        $ch = curl_init(self::apiBase() . '/transactions/' . rawurlencode($id));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $j = $r ? json_decode($r, true) : null;
        return ($code === 200 && isset($j['data'])) ? $j['data'] : null;
    }
}
