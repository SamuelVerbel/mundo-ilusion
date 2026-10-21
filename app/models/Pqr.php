<?php
// Peticiones, quejas, reclamos y sugerencias. El cliente las crea; el administrador las responde (y se avisa por correo).
class Pqr
{
    const TIPOS = ['peticion' => 'Petición', 'queja' => 'Queja', 'reclamo' => 'Reclamo', 'sugerencia' => 'Sugerencia'];
    const SEL = 'SELECT q.*, u.nombre AS cliente, u.correo AS cliente_correo, u.telefono AS cliente_tel, r.nombre AS respondio
                 FROM pqr q JOIN usuario u ON u.id_usuario = q.id_usuario LEFT JOIN usuario r ON r.id_usuario = q.id_usuario_respuesta';

    public static function listar(string $estado = '', string $tipo = ''): array
    {
        $w = []; $p = [];
        if (in_array($estado, ['pendiente', 'respondida', 'cerrada'], true)) { $w[] = 'q.estado = ?'; $p[] = $estado; }
        if (isset(self::TIPOS[$tipo])) { $w[] = 'q.tipo = ?'; $p[] = $tipo; }
        return Database::all(self::SEL . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . " ORDER BY q.estado = 'pendiente' DESC, q.id_pqr DESC LIMIT 200", $p);
    }
    public static function find(int $id): ?array { return Database::one(self::SEL . ' WHERE q.id_pqr = ?', [$id]); }
    public static function pendientes(): int { return (int) Database::val("SELECT COUNT(*) FROM pqr WHERE estado = 'pendiente'"); }
    public static function crear(int $uid, string $tipo, string $asunto, string $mensaje): int
    {
        return Database::insert('INSERT INTO pqr (id_usuario, tipo, asunto, mensaje) VALUES (?,?,?,?)', [$uid, $tipo, $asunto, $mensaje]);
    }
    /** Guarda la respuesta y se la envía al cliente por correo. */
    public static function responder(int $id, string $respuesta, int $uidAdmin): bool
    {
        $q = self::find($id); if (!$q || $q['estado'] === 'cerrada') return false;
        Database::exec("UPDATE pqr SET respuesta = ?, estado = 'respondida', id_usuario_respuesta = ?, fecha_respuesta = NOW() WHERE id_pqr = ?", [$respuesta, $uidAdmin, $id]);
        Mailer::send($q['cliente_correo'], 'Respuesta a tu ' . mb_strtolower(self::TIPOS[$q['tipo']]) . ': ' . $q['asunto'], Mailer::plantilla('Respondimos tu mensaje',
            '<p>Hola ' . e($q['cliente']) . ',</p><p><b>Tu mensaje:</b><br>' . nl2br(e($q['mensaje'])) . '</p><p><b>Nuestra respuesta:</b><br>' . nl2br(e($respuesta)) . '</p><p>Gracias por ayudarnos a mejorar 💖</p>'));
        return true;
    }
    public static function cerrar(int $id): void { Database::exec("UPDATE pqr SET estado = 'cerrada' WHERE id_pqr = ? AND estado = 'respondida'", [$id]); }
}
