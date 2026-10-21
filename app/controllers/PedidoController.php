<?php
class PedidoController extends Controller
{
    private function minFecha(): string { return date('Y-m-d', time() + ((int) conf('anticipacion_horas', 24)) * 3600); }

    public function checkout(): void
    {
        $items = Carrito::items(Auth::id());
        if (!$items) { flash('warning', 'Tu carrito está vacío.'); redirect('catalogo'); }
        $this->view('pedidos/checkout', ['title' => 'Finalizar pedido', 'items' => $items, 'r' => Carrito::resumen($items), 'direcciones' => Direccion::deUsuario(Auth::id()), 'minFecha' => $this->minFecha()]);
    }
    public function crear(): void
    {
        $uid = Auth::id();
        $modalidad = $this->post('modalidad'); $fecha = $this->post('fecha_entrega'); $ded = $this->post('dedicatoria'); $obs = $this->post('observaciones');
        $v = (new Validator())->in('modalidad', $modalidad, ['domicilio', 'recogida'], 'Elige cómo quieres recibir tu pedido.')
            ->required('fecha_entrega', $fecha, 'La fecha de entrega')
            ->regex('fecha_entrega', $fecha, '/^\d{4}-\d{2}-\d{2}$/', 'La fecha no es válida.')
            ->custom('fecha_entrega', $fecha >= $this->minFecha(), 'La fecha debe respetar la anticipación mínima de ' . conf('anticipacion_horas', 24) . ' horas (desde el ' . fecha($this->minFecha()) . ').')
            ->max('dedicatoria', $ded, 255, 'La dedicatoria')->max('observaciones', $obs, 500, 'Las observaciones')
            ->custom('politicas', !empty($_POST['politicas']), 'Debes aceptar las políticas de entrega, cambios y devoluciones.');
        $idDir = null;
        if ($modalidad === 'domicilio' && !isset($v->errors['modalidad'])) {
            if (($_POST['id_direccion'] ?? '') === 'nueva') {
                $dir = $this->post('direccion'); $barrio = $this->post('barrio');
                $v->required('direccion', $dir, 'La dirección')->required('barrio', $barrio, 'El barrio')->max('direccion', $dir, 200, 'La dirección');
                if (!$v->fails()) $idDir = Direccion::crear($uid, $dir, $barrio, $this->post('referencia'));
            } else {
                $d = Direccion::find((int) ($_POST['id_direccion'] ?? 0), $uid);
                $v->custom('id_direccion', (bool) $d, 'Selecciona una dirección de entrega.');
                $idDir = $d ? (int) $d['id_direccion'] : null;
            }
        }
        if ($v->fails()) $this->back($v->errors, 'checkout');
        try {
            $id = Pedido::crearDesdeCarrito($uid, ['modalidad' => $modalidad, 'id_direccion' => $idDir, 'fecha_entrega' => $fecha, 'dedicatoria' => $ded, 'observaciones' => $obs]);
        } catch (RuntimeException $e) { $this->back(['x' => $e->getMessage()], 'carrito'); }
        flash('success', 'Pedido #' . $id . ' creado. Realiza el pago para confirmarlo.');
        redirect('pedidos/' . $id);
    }
    public function index(): void
    {
        Pedido::expirarVencidos();
        $this->view('pedidos/index', ['title' => 'Mis pedidos', 'pedidos' => Pedido::deUsuario(Auth::id())]);
    }
    public function show(string $id): void
    {
        Pedido::expirarVencidos();
        $p = Pedido::find((int) $id, Auth::id());
        if (!$p) abort(404, 'No encontramos ese pedido.');
        $this->view('pedidos/show', ['title' => 'Pedido #' . $p['id_pedido'], 'p' => $p, 'detalles' => Pedido::detalles((int) $id), 'historial' => Pedido::historialDe((int) $id), 'pagos' => Pedido::pagos((int) $id),
            'puedePagar' => Pedido::puedePagar($p), 'wompi' => Wompi::configurado()]);
    }
    public function cancelar(string $id): void
    {
        $err = Pedido::cancelarPorCliente((int) $id, Auth::id());
        flash($err ? 'danger' : 'success', $err ?: 'Pedido cancelado. Si ya habías pagado, la tienda gestionará la devolución de tu dinero.');
        redirect('pedidos/' . (int) $id);
    }
    /** Crea un intento de pago y envía al cliente al checkout de Wompi. */
    public function pagar(string $id): void
    {
        $p = Pedido::find((int) $id, Auth::id());
        if (!$p || !Pedido::puedePagar($p)) { flash('danger', 'Este pedido ya no se puede pagar.'); redirect('pedidos/' . (int) $id); }
        if (!Wompi::configurado()) { flash('danger', 'El pago en línea aún no está configurado (faltan las llaves de Wompi en el archivo .env).'); redirect('pedidos/' . (int) $id); }
        $pago = Pago::crearIntento($p);
        redirect(Wompi::checkoutUrl($pago, absolute_url('pago/retorno')));
    }
    /** Wompi devuelve al cliente aquí con ?id=TRANSACCION. Se consulta la transacción a Wompi (reconciliación) y se aplica el resultado. */
    public function retorno(): void
    {
        $tx = Wompi::consultarTransaccion((string) ($_GET['id'] ?? ''));
        if (!$tx) { flash('info', 'Estamos verificando tu pago. Revisa el estado de tu pedido en unos minutos.'); redirect('pedidos'); }
        $r = Pago::aplicarResultado((string) $tx['reference'], (string) $tx['status'], (string) $tx['id'], $tx['payment_method_type'] ?? null, (int) $tx['amount_in_cents']);
        $msg = ['aprobado' => ['success', '¡Pago aprobado! Tu pedido quedó recibido.'], 'rechazado' => ['danger', 'El pago fue rechazado. Puedes intentarlo de nuevo.'], 'pendiente' => ['info', 'Tu pago está en verificación.']][$r['estado'] ?? ''] ?? ['warning', 'No pudimos confirmar el pago. Si te descontaron, comunícate con la tienda.'];
        flash($msg[0], $msg[1]);
        redirect(!empty($r['id_pedido']) ? 'pedidos/' . $r['id_pedido'] : 'pedidos');
    }
    /** SOLO DESARROLLO: simula el resultado de Wompi para probar el flujo sin llaves. */
    public function simular(string $id): void
    {
        if (APP_ENV !== 'local') abort(404);
        $p = Pedido::find((int) $id, Auth::id());
        if (!$p || !Pedido::puedePagar($p)) { flash('danger', 'Este pedido ya no se puede pagar.'); redirect('pedidos/' . (int) $id); }
        $pago = Pago::crearIntento($p);
        $ok = ($_POST['resultado'] ?? '') === 'aprobado';
        Pago::aplicarResultado($pago['referencia'], $ok ? 'APPROVED' : 'DECLINED', 'SIM-' . random_int(1000, 9999), 'SIMULADO', null);
        flash($ok ? 'success' : 'danger', $ok ? '[Simulación] Pago aprobado.' : '[Simulación] Pago rechazado.');
        redirect('pedidos/' . (int) $id);
    }
}
