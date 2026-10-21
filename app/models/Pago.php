<?php
class Pago
{
    const MAPA = ['APPROVED' => 'aprobado', 'DECLINED' => 'rechazado', 'ERROR' => 'error', 'VOIDED' => 'anulado', 'PENDING' => 'pendiente'];

    /** Cada intento de pago usa una referencia nueva y única (RF29). */
    public static function crearIntento(array $pedido): array
    {
        $ref = 'MI-' . $pedido['id_pedido'] . '-' . time() . '-' . strtoupper(bin2hex(random_bytes(2)));
        $id = Database::insert('INSERT INTO pago (id_pedido, referencia, monto, moneda) VALUES (?,?,?,?)', [$pedido['id_pedido'], $ref, $pedido['total'], 'COP']);
        return ['id_pago' => $id, 'referencia' => $ref, 'monto' => (float) $pedido['total']];
    }
    public static function porReferencia(string $ref): ?array { return Database::one('SELECT * FROM pago WHERE referencia = ?', [$ref]); }

    /**
     * Aplica el resultado de un pago. Es IDEMPOTENTE: si el mismo resultado llega dos veces no se duplica nada (RNF10).
     * Lo usan el webhook de Wompi, la reconciliación al volver del checkout y el botón de simulación (solo desarrollo).
     */
    public static function aplicarResultado(string $referencia, string $estadoWompi, ?string $idTx, ?string $metodo, ?int $montoCents): array
    {
        $nuevo = self::MAPA[strtoupper($estadoWompi)] ?? 'error';
        $notificar = null;
        Database::begin();
        try {
            $pago = Database::one('SELECT * FROM pago WHERE referencia = ? FOR UPDATE', [$referencia]);
            if (!$pago) { Database::rollback(); return ['ok' => false, 'msg' => 'Referencia desconocida', 'id_pago' => null]; }
            if ($montoCents !== null && (int) round((float) $pago['monto'] * 100) !== $montoCents) {
                Database::rollback(); error_log("[Pago] Monto no coincide en $referencia"); return ['ok' => false, 'msg' => 'El monto no coincide', 'id_pago' => (int) $pago['id_pago']];
            }
            if (in_array($pago['estado'], ['aprobado', 'reembolsado'], true)) { Database::commit(); return ['ok' => true, 'msg' => 'Ya procesado', 'id_pago' => (int) $pago['id_pago'], 'estado' => $pago['estado'], 'id_pedido' => (int) $pago['id_pedido']]; }
            Database::exec('UPDATE pago SET estado = ?, id_transaccion_wompi = COALESCE(?, id_transaccion_wompi), metodo_pago = COALESCE(?, metodo_pago), fecha_pago = IF(? = \'aprobado\', NOW(), fecha_pago) WHERE id_pago = ?',
                [$nuevo, $idTx, $metodo, $nuevo, $pago['id_pago']]);
            if ($nuevo === 'aprobado') {
                $pedido = Database::one('SELECT * FROM pedido WHERE id_pedido = ? FOR UPDATE', [$pago['id_pedido']]);
                if ($pedido['estado'] === 'pendiente_pago') {
                    Database::exec("UPDATE pedido SET estado = 'recibido' WHERE id_pedido = ?", [$pedido['id_pedido']]);
                    Pedido::historial((int) $pedido['id_pedido'], null, 'pendiente_pago', 'recibido', 'Pago aprobado (' . $referencia . ')');
                    $notificar = (int) $pedido['id_pedido'];
                } elseif ($pedido['estado'] === 'cancelado') {
                    Pedido::historial((int) $pedido['id_pedido'], null, 'cancelado', 'cancelado', 'Pago aprobado en un pedido cancelado: requiere devolución (' . $referencia . ')');
                }
            }
            Database::commit();
        } catch (Throwable $e) { Database::rollback(); throw $e; }
        if ($notificar) Notificacion::pedidoPagado($notificar);      // CU31 + aviso al personal (RF26)
        return ['ok' => true, 'msg' => 'Procesado', 'id_pago' => (int) $pago['id_pago'], 'estado' => $nuevo, 'id_pedido' => (int) $pago['id_pedido']];
    }
    public static function registrarEvento(?int $idPago, string $idEvento, string $tipo, string $payload, bool $firmaValida): bool
    {
        // INSERT IGNORE: si el evento ya existe (mismo checksum) no se inserta de nuevo
        return Database::exec('INSERT IGNORE INTO evento_pago (id_pago, id_evento_wompi, tipo, payload, firma_valida) VALUES (?,?,?,?,?)', [$idPago, $idEvento, $tipo, $payload, $firmaValida ? 1 : 0]) > 0;
    }
    public static function marcarEventoProcesado(string $idEvento): void { Database::exec('UPDATE evento_pago SET procesado = 1 WHERE id_evento_wompi = ?', [$idEvento]); }
    /** El administrador registra que devolvió el dinero de un pago aprobado (RF24). */
    public static function reembolsar(int $idPedido, int $uid): ?string
    {
        $pago = Database::one("SELECT * FROM pago WHERE id_pedido = ? AND estado = 'aprobado' ORDER BY id_pago DESC LIMIT 1", [$idPedido]);
        if (!$pago) return 'Este pedido no tiene un pago aprobado para devolver.';
        Database::exec("UPDATE pago SET estado = 'reembolsado', fecha_reembolso = NOW(), id_usuario_reembolso = ? WHERE id_pago = ?", [$uid, $pago['id_pago']]);
        $p = Pedido::find($idPedido);
        Pedido::historial($idPedido, $uid, $p['estado'], $p['estado'], 'Devolución del dinero registrada (' . $pago['referencia'] . ')');
        return null;
    }
}
