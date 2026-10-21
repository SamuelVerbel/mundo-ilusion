<?php
// Envío de correos. Si MAIL_ENABLED=false (modo desarrollo) guarda cada correo como archivo en storage/mail/.
class Mailer
{
    public static function send(string $to, string $subject, string $html): bool
    {
        $ok = false;
        if (filter_var(env('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN) && is_file(ROOT_PATH . '/app/libs/PHPMailer/PHPMailer.php')) {
            try {
                require_once ROOT_PATH . '/app/libs/PHPMailer/Exception.php';
                require_once ROOT_PATH . '/app/libs/PHPMailer/PHPMailer.php';
                require_once ROOT_PATH . '/app/libs/PHPMailer/SMTP.php';
                $m = new PHPMailer\PHPMailer\PHPMailer(true);
                $m->isSMTP(); $m->Host = (string) env('MAIL_HOST'); $m->SMTPAuth = true;
                $m->Username = (string) env('MAIL_USER'); $m->Password = (string) env('MAIL_PASS');
                $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; $m->Port = (int) env('MAIL_PORT', 587);
                $m->CharSet = 'UTF-8'; $m->Timeout = 10;
                $m->setFrom((string) env('MAIL_FROM', env('MAIL_USER')), (string) env('MAIL_FROM_NAME', APP_NAME));
                $m->addAddress($to); $m->isHTML(true); $m->Subject = $subject; $m->Body = $html; $m->AltBody = strip_tags($html);
                $m->send(); $ok = true;
            } catch (Throwable $e) {
                error_log('[Mailer] ' . $e->getMessage());   // RNF22: el error se registra y no rompe el flujo
            }
        }
        if (!$ok && !filter_var(env('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN)) {
            $file = ROOT_PATH . '/storage/mail/' . date('Ymd-His') . '-' . substr(md5($to . $subject . microtime()), 0, 6) . '.html';
            file_put_contents($file, "<!-- Para: $to | Asunto: $subject -->\n$html");
            $ok = true;
        }
        return $ok;
    }
    public static function plantilla(string $titulo, string $cuerpo): string
    {
        return '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;border:1px solid #f3c6da;border-radius:12px;overflow:hidden">'
            . '<div style="background:#E0457B;color:#fff;padding:16px 24px;font-size:20px">✿ ' . e(APP_NAME) . '</div>'
            . '<div style="padding:24px"><h2 style="color:#E0457B;margin-top:0">' . e($titulo) . '</h2>' . $cuerpo . '</div></div>';
    }
}
