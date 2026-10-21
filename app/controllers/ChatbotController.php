<?php
// Endpoints JSON del asistente virtual. Protegidos con CSRF (en el Router) y con límite de mensajes por sesión.
class ChatbotController extends Controller
{
    const LIMITE_VENTANA = 20;   // mensajes
    const VENTANA_SEG = 600;     // por cada 10 minutos

    private function json(array $d, int $code = 200): never { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

    public function mensaje(): void
    {
        if (!Chatbot::enabled()) $this->json(['ok' => false, 'error' => 'El asistente no está disponible.'], 503);
        $ahora = time();
        $_SESSION['_chat_t'] = array_values(array_filter($_SESSION['_chat_t'] ?? [], fn($x) => $ahora - $x < self::VENTANA_SEG));
        if (count($_SESSION['_chat_t']) >= self::LIMITE_VENTANA) $this->json(['ok' => false, 'error' => 'Estás escribiendo muy rápido 😊. Espera unos minutos o escríbenos por WhatsApp.'], 429);
        $_SESSION['_chat_t'][] = $ahora;

        $msg = $this->post('mensaje');
        if ($msg === '' || mb_strlen($msg) > Chatbot::MAX_MENSAJE) $this->json(['ok' => false, 'error' => 'Escribe un mensaje de hasta ' . Chatbot::MAX_MENSAJE . ' caracteres.'], 422);

        $r = Chatbot::responder($msg, $_SESSION['_chat'] ?? []);
        $h = $_SESSION['_chat'] ?? [];
        $h[] = ['role' => 'user', 'content' => $msg]; $h[] = ['role' => 'assistant', 'content' => $r['texto']];
        $_SESSION['_chat'] = array_slice($h, -12);
        $this->json(['ok' => true, 'respuesta' => $r['texto'], 'modo' => $r['modo']]);
    }

    public function reiniciar(): void { unset($_SESSION['_chat']); $this->json(['ok' => true]); }
}
