<?php
// Recibe los eventos de Wompi (URL de eventos). Sin sesión ni CSRF: se protege con la firma (checksum).
class WebhookController extends Controller
{
    public function wompi(): void
    {
        header('Content-Type: application/json');
        $raw = file_get_contents('php://input') ?: '';
        $e = json_decode($raw, true);
        if (!is_array($e) || !isset($e['signature']['checksum'])) { http_response_code(400); echo '{"ok":false}'; return; }
        if (!Wompi::eventoValido($e, (string) env('WOMPI_EVENTS_SECRET', ''))) {
            Pago::registrarEvento(null, 'inv-' . hash('sha256', $raw), (string) ($e['event'] ?? '?'), $raw, false);
            http_response_code(400); echo '{"ok":false,"error":"firma"}'; return;
        }
        $idEvento = (string) $e['signature']['checksum'];
        $tx = $e['data']['transaction'] ?? null;
        if (($e['event'] ?? '') !== 'transaction.updated' || !$tx) { echo '{"ok":true,"ignorado":true}'; return; }
        try {
            $pago = Pago::porReferencia((string) $tx['reference']);
            $nuevo = Pago::registrarEvento($pago ? (int) $pago['id_pago'] : null, $idEvento, 'transaction.updated', $raw, true);
            if (!$nuevo) { echo '{"ok":true,"duplicado":true}'; return; }          // mismo evento ya recibido (RNF10)
            if ($pago) { Pago::aplicarResultado((string) $tx['reference'], (string) $tx['status'], (string) $tx['id'], $tx['payment_method_type'] ?? null, (int) $tx['amount_in_cents']); }
            Pago::marcarEventoProcesado($idEvento);
            echo '{"ok":true}';
        } catch (Throwable $ex) { error_log('[Webhook] ' . $ex->getMessage()); http_response_code(500); echo '{"ok":false}'; }   // Wompi reintentará
    }
}
