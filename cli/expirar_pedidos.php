<?php
// Cancela los pedidos sin pagar que ya expiraron. Uso:  php cli/expirar_pedidos.php
// (La web también lo hace sola al abrir pedidos; esto es para programarlo con el Programador de tareas o cron.)
require dirname(__DIR__) . '/config/config.php';
spl_autoload_register(function ($c) { foreach (['core', 'models'] as $d) { $f = ROOT_PATH . "/app/$d/$c.php"; if (is_file($f)) { require $f; return; } } });
echo 'Pedidos cancelados por expiración: ' . Pedido::expirarVencidos() . PHP_EOL;
