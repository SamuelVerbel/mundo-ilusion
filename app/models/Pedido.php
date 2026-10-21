<?php
class Pedido
{
    /** Ciclo de vida permitido para el personal (el paso pendiente_pago -> recibido solo lo hace el pago). */
    const SIGUIENTES = [
        'recibido' => ['confirmado'], 'confirmado' => ['en_preparacion'],
        'en_preparacion' => ['en_camino', 'listo_para_recoger'], 'en_camino' => ['entregado'], 'listo_para_recoger' => ['entregado'],
    ];

    /** Crea el pedido desde el carrito en UNA transacción (todo o nada). Lanza RuntimeException con mensaje para el usuario. */
    public static function crearDesdeCarrito(int $uid, array $d): int
    {
        Database::begin();
        try {
            $items = Carrito::items($uid);
            if (!$items) throw new RuntimeException('Tu carrito está vacío.');
            foreach ($items as $i) if (!$i['comprable']) throw new RuntimeException('"' . $i['nombre'] . '" ya no está disponible. Quítalo del carrito para continuar.');
            $r = Carrito::resumen($items);                       // precios calculados en el SERVIDOR
            $horas = (int) Configuracion::get('expiracion_pedido_horas', 24);
            $idPedido = Database::insert(
                'INSERT INTO pedido (id_usuario, id_direccion, modalidad_entrega, fecha_entrega, dedicatoria, observaciones, subtotal, descuento_total, total, estado, fecha_expiracion)
                 VALUES (?,?,?,?,?,?,?,?,?,?, DATE_ADD(NOW(), INTERVAL ' . $horas . ' HOUR))',
                [$uid, $d['modalidad'] === 'domicilio' ? $d['id_direccion'] : null, $d['modalidad'], $d['fecha_entrega'], $d['dedicatoria'] ?: null, $d['observaciones'] ?: null, $r['subtotal'], $r['descuento'], $r['total'], 'pendiente_pago']);
            foreach ($items as $i) {
                Database::exec('INSERT INTO detalle_pedido (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario, porcentaje_descuento, subtotal) VALUES (?,?,?,?,?,?,?)',
                    [$idPedido, $i['id_producto'], $i['nombre'], $i['cantidad'], $i['precio'], $i['descuento'], $i['subtotal']]);
            }
            self::historial($idPedido, $uid, null, 'pendiente_pago', 'Pedido creado por el cliente');
            Database::exec("UPDATE carrito SET estado = 'convertido' WHERE id_usuario = ? AND estado = 'activo'", [$uid]);
            Database::commit();
            return $idPedido;
        } catch (Throwable $e) { Database::rollback(); throw $e; }
    }

    public static function historial(int $id, ?int $uid, ?string $ant, string $nuevo, ?string $nota = null): void
    {
        Database::insert('INSERT INTO pedido_estado_historial (id_pedido, id_usuario, estado_anterior, estado_nuevo, nota) VALUES (?,?,?,?,?)', [$id, $uid, $ant, $nuevo, $nota]);
    }
    const SEL = "SELECT p.*, u.nombre AS cliente, u.correo AS cliente_correo, u.telefono AS cliente_telefono,
        d.direccion AS dir_direccion, d.barrio AS dir_barrio, d.referencia AS dir_referencia,
        (SELECT pg.estado FROM pago pg WHERE pg.id_pedido = p.id_pedido ORDER BY pg.id_pago DESC LIMIT 1) AS estado_pago
        FROM pedido p JOIN usuario u ON u.id_usuario = p.id_usuario LEFT JOIN direccion d ON d.id_direccion = p.id_direccion";

    public static function find(int $id, ?int $uid = null): ?array
    {
        return Database::one(self::SEL . ' WHERE p.id_pedido = ?' . ($uid ? ' AND p.id_usuario = ?' : ''), $uid ? [$id, $uid] : [$id]);
    }
    public static function deUsuario(int $uid): array { return Database::all(self::SEL . ' WHERE p.id_usuario = ? ORDER BY p.id_pedido DESC', [$uid]); }
    public static function detalles(int $id): array { return Database::all('SELECT * FROM detalle_pedido WHERE id_pedido = ? ORDER BY id_detalle_pedido', [$id]); }
    public static function historialDe(int $id): array
    {
        return Database::all('SELECT h.*, u.nombre AS usuario FROM pedido_estado_historial h LEFT JOIN usuario u ON u.id_usuario = h.id_usuario WHERE h.id_pedido = ? ORDER BY h.id_historial', [$id]);
    }
    public static function pagos(int $id): array { return Database::all('SELECT * FROM pago WHERE id_pedido = ? ORDER BY id_pago DESC', [$id]); }

    public static function listar(array $f, int $page = 1, int $per = 15): array
    {
        $w = []; $p = [];
        if (!empty($f['estado'])) { $w[] = 'p.estado = ?'; $p[] = $f['estado']; }
        if (!empty($f['desde'])) { $w[] = 'DATE(p.fecha_pedido) >= ?'; $p[] = $f['desde']; }
        if (!empty($f['hasta'])) { $w[] = 'DATE(p.fecha_pedido) <= ?'; $p[] = $f['hasta']; }
        if (!empty($f['q'])) { $w[] = '(u.nombre LIKE ? OR p.id_pedido = ?)'; $p[] = '%' . $f['q'] . '%'; $p[] = (int) $f['q']; }
        $ws = $w ? ' WHERE ' . implode(' AND ', $w) : '';
        $total = (int) Database::val("SELECT COUNT(*) FROM pedido p JOIN usuario u ON u.id_usuario = p.id_usuario$ws", $p);
        $pages = max(1, (int) ceil($total / $per)); $page = min(max(1, $page), $pages);
        $items = Database::all(self::SEL . "$ws ORDER BY p.id_pedido DESC LIMIT " . (int) $per . ' OFFSET ' . (($page - 1) * $per), $p);
        return ['items' => $items, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    /** Estados a los que puede pasar este pedido según el rol de quien lo gestiona. */
    public static function siguientes(array $pedido, string $rol): array
    {
        $s = self::SIGUIENTES[$pedido['estado']] ?? [];
        $s = array_values(array_filter($s, fn($e) => !($e === 'en_camino' && $pedido['modalidad_entrega'] !== 'domicilio') && !($e === 'listo_para_recoger' && $pedido['modalidad_entrega'] !== 'recogida')));
        if ($rol === 'administrador' && !in_array($pedido['estado'], ['entregado', 'cancelado'], true)) $s[] = 'cancelado';
        return $s;
    }
    public static function cambiarEstado(int $id, string $nuevo, int $uid, string $rol, ?string $nota = null): ?string
    {
        Database::begin();
        try {
            $p = Database::one('SELECT * FROM pedido WHERE id_pedido = ? FOR UPDATE', [$id]);
            if (!$p) { Database::rollback(); return 'El pedido no existe.'; }
            if (!in_array($nuevo, self::siguientes($p, $rol), true)) { Database::rollback(); return 'Ese cambio de estado no está permitido para este pedido.'; }
            Database::exec('UPDATE pedido SET estado = ? WHERE id_pedido = ?', [$nuevo, $id]);
            self::historial($id, $uid, $p['estado'], $nuevo, $nota);
            Database::commit();
            return null;
        } catch (Throwable $e) { Database::rollback(); throw $e; }
    }
    /** El cliente solo cancela en pendiente_pago o recibido (CU16). */
    public static function cancelarPorCliente(int $id, int $uid): ?string
    {
        Database::begin();
        try {
            $p = Database::one('SELECT * FROM pedido WHERE id_pedido = ? AND id_usuario = ? FOR UPDATE', [$id, $uid]);
            if (!$p) { Database::rollback(); return 'El pedido no existe.'; }
            if (!in_array($p['estado'], ['pendiente_pago', 'recibido'], true)) { Database::rollback(); return 'Este pedido ya no se puede cancelar. Comunícate con la tienda.'; }
            $pagado = (bool) Database::val("SELECT 1 FROM pago WHERE id_pedido = ? AND estado = 'aprobado' LIMIT 1", [$id]);
            Database::exec("UPDATE pedido SET estado = 'cancelado' WHERE id_pedido = ?", [$id]);
            self::historial($id, $uid, $p['estado'], 'cancelado', $pagado ? 'Cancelado por el cliente. Pago aprobado: requiere devolución' : 'Cancelado por el cliente');
            Database::commit();
            return null;
        } catch (Throwable $e) { Database::rollback(); throw $e; }
    }
    /** RF29: los pedidos sin pagar que superan el tiempo configurado se cancelan solos. */
    public static function expirarVencidos(): int
    {
        $ids = array_column(Database::all("SELECT id_pedido FROM pedido WHERE estado = 'pendiente_pago' AND fecha_expiracion < NOW()"), 'id_pedido');
        $n = 0;
        foreach ($ids as $id) {
            if (Database::exec("UPDATE pedido SET estado = 'cancelado' WHERE id_pedido = ? AND estado = 'pendiente_pago'", [$id])) { self::historial((int) $id, null, 'pendiente_pago', 'cancelado', 'Expiró sin pago'); $n++; }
        }
        return $n;
    }
    public static function puedePagar(array $p): bool { return $p['estado'] === 'pendiente_pago' && strtotime($p['fecha_expiracion']) > time(); }

    public static function estadisticas(): array
    {
        return [
            'por_estado' => array_column(Database::all('SELECT estado, COUNT(*) n FROM pedido GROUP BY estado'), 'n', 'estado'),
            'ventas_hoy' => (float) Database::val("SELECT COALESCE(SUM(total),0) FROM pedido WHERE estado NOT IN ('pendiente_pago','cancelado') AND DATE(fecha_pedido) = CURDATE()"),
            'ventas_mes' => (float) Database::val("SELECT COALESCE(SUM(total),0) FROM pedido WHERE estado NOT IN ('pendiente_pago','cancelado') AND YEAR(fecha_pedido) = YEAR(CURDATE()) AND MONTH(fecha_pedido) = MONTH(CURDATE())"),
            'clientes' => (int) Database::val("SELECT COUNT(*) FROM usuario WHERE rol = 'cliente'"),
            'productos' => (int) Database::val("SELECT COUNT(*) FROM producto WHERE estado = 'activo'"),
        ];
    }
}
