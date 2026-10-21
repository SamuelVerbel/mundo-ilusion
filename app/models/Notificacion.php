<?php
// Correos del sistema: confirmación al cliente (RF25) y aviso al negocio (RF26).
class Notificacion
{
    public static function pedidoPagado(int $idPedido): void
    {
        $p = Pedido::find($idPedido); if (!$p) return;
        $det = Pedido::detalles($idPedido);
        $filas = '';
        foreach ($det as $d) $filas .= '<tr><td style="padding:4px 8px">' . e($d['nombre_producto']) . ' × ' . (int) $d['cantidad'] . '</td><td style="padding:4px 8px;text-align:right">' . cop($d['subtotal']) . '</td></tr>';
        $entrega = $p['modalidad_entrega'] === 'domicilio' ? 'Domicilio: ' . e($p['dir_direccion'] . ', ' . $p['dir_barrio']) : 'Recogida en la tienda';
        $resumen = '<table style="width:100%;border-collapse:collapse;font-size:14px">' . $filas . '<tr><td style="padding:8px;border-top:1px solid #eee"><b>Total</b></td><td style="padding:8px;border-top:1px solid #eee;text-align:right"><b>' . cop($p['total']) . '</b></td></tr></table>'
            . '<p style="font-size:14px">' . $entrega . '<br>Fecha: ' . fecha($p['fecha_entrega']) . '</p>';
        Mailer::send($p['cliente_correo'], "Pedido #$idPedido confirmado", Mailer::plantilla('¡Gracias por tu compra, ' . $p['cliente'] . '!', '<p>Recibimos tu pago y tu pedido <b>#' . $idPedido . '</b> quedó registrado.</p>' . $resumen));
        $negocio = Configuracion::get('correo_negocio');
        if ($negocio) Mailer::send($negocio, "Nuevo pedido pagado #$idPedido", Mailer::plantilla('Nuevo pedido pagado', '<p>Cliente: ' . e($p['cliente']) . '</p>' . $resumen));
    }
}
