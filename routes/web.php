<?php
// Mapa de rutas: método + URL => [Controlador, acción]. 'roles' protege por tipo de usuario.
$cli   = ['roles' => ['cliente']];
$login = ['roles' => ['cliente', 'empleado', 'administrador']];
$staff = ['roles' => ['empleado', 'administrador']];
$admin = ['roles' => ['administrador']];

// ---- Público ----
$router->get('/',                 [HomeController::class, 'index']);
$router->get('/catalogo',         [CatalogoController::class, 'index']);
$router->get('/producto/{id}',    [CatalogoController::class, 'show']);
$router->get('/promociones',      [CatalogoController::class, 'promociones']);
$router->get('/tienda',           [TiendaController::class, 'info']);

// ---- Diagnóstico (solo APP_ENV=local) ----
$router->get('/diagnostico',     [DiagnosticoController::class, 'index']);

// ---- Cuenta ----
$router->get('/login',            [AuthController::class, 'loginForm']);
$router->post('/login',           [AuthController::class, 'login']);
$router->get('/registro',         [AuthController::class, 'registroForm']);
$router->post('/registro',        [AuthController::class, 'registro']);
$router->post('/logout',          [AuthController::class, 'logout']);
$router->get('/recuperar',        [AuthController::class, 'olvidoForm']);
$router->post('/recuperar',       [AuthController::class, 'olvido']);
$router->get('/restablecer/{token}',  [AuthController::class, 'resetForm']);
$router->post('/restablecer/{token}', [AuthController::class, 'reset']);
$router->get('/perfil',           [PerfilController::class, 'index'], $login);
$router->post('/perfil',          [PerfilController::class, 'update'], $login);
$router->post('/perfil/password', [PerfilController::class, 'password'], $login);
$router->post('/perfil/direcciones',                    [PerfilController::class, 'direccionCrear'], $cli);
$router->post('/perfil/direcciones/{id}/desactivar',    [PerfilController::class, 'direccionDesactivar'], $cli);

// ---- Cliente: carrito, pedidos, pago, PQR ----
$router->get('/carrito',          [CarritoController::class, 'index'], $cli);
$router->post('/carrito/agregar', [CarritoController::class, 'agregar']);      // si no hay sesión, lo manda a iniciar sesión
$router->post('/carrito/actualizar', [CarritoController::class, 'actualizar'], $cli);
$router->post('/carrito/eliminar',   [CarritoController::class, 'eliminar'], $cli);
$router->get('/checkout',         [PedidoController::class, 'checkout'], $cli);
$router->post('/checkout',        [PedidoController::class, 'crear'], $cli);
$router->get('/pedidos',          [PedidoController::class, 'index'], $cli);
$router->get('/pedidos/{id}',     [PedidoController::class, 'show'], $cli);
$router->post('/pedidos/{id}/cancelar',      [PedidoController::class, 'cancelar'], $cli);
$router->post('/pedidos/{id}/pagar',         [PedidoController::class, 'pagar'], $cli);
$router->post('/pedidos/{id}/simular-pago',  [PedidoController::class, 'simular'], $cli);
$router->get('/pago/retorno',     [PedidoController::class, 'retorno'], $cli);
$router->get('/pqr',              [PqrController::class, 'form'], $cli);
$router->post('/pqr',             [PqrController::class, 'guardar'], $cli);

// ---- Asistente virtual (público; JSON) ----
$router->post('/chatbot/mensaje',   [ChatbotController::class, 'mensaje']);
$router->post('/chatbot/reiniciar', [ChatbotController::class, 'reiniciar']);

// ---- Wompi (sin sesión ni CSRF: se valida con la firma) ----
$router->post('/webhooks/wompi',  [WebhookController::class, 'wompi'], ['csrf' => false]);

// ---- Panel: empleado y administrador ----
$router->get('/admin',                      [AdminController::class, 'dashboard'], $staff);
$router->get('/admin/pedidos',              [AdminPedidoController::class, 'index'], $staff);
$router->get('/admin/pedidos/{id}',         [AdminPedidoController::class, 'show'], $staff);
$router->post('/admin/pedidos/{id}/estado', [AdminPedidoController::class, 'estado'], $staff);
$router->post('/admin/pedidos/{id}/devolucion', [AdminPedidoController::class, 'devolucion'], $admin);

// ---- Panel: solo administrador ----
$router->get('/admin/productos',                 [AdminProductoController::class, 'index'], $admin);
$router->get('/admin/productos/nuevo',           [AdminProductoController::class, 'nuevo'], $admin);
$router->post('/admin/productos',                [AdminProductoController::class, 'crear'], $admin);
$router->get('/admin/productos/{id}/editar',     [AdminProductoController::class, 'editar'], $admin);
$router->post('/admin/productos/{id}',           [AdminProductoController::class, 'actualizar'], $admin);
$router->post('/admin/productos/{id}/estado',    [AdminProductoController::class, 'estado'], $admin);
$router->post('/admin/productos/{id}/eliminar',  [AdminProductoController::class, 'eliminar'], $admin);
$router->post('/admin/productos/{id}/imagenes/{img}/eliminar',  [AdminProductoController::class, 'imagenEliminar'], $admin);
$router->post('/admin/productos/{id}/imagenes/{img}/principal', [AdminProductoController::class, 'imagenPrincipal'], $admin);

$router->get('/admin/categorias',                [AdminCategoriaController::class, 'index'], $admin);
$router->get('/admin/categorias/nuevo',          [AdminCategoriaController::class, 'nuevo'], $admin);
$router->post('/admin/categorias',               [AdminCategoriaController::class, 'crear'], $admin);
$router->get('/admin/categorias/{id}/editar',    [AdminCategoriaController::class, 'editar'], $admin);
$router->post('/admin/categorias/{id}',          [AdminCategoriaController::class, 'actualizar'], $admin);
$router->post('/admin/categorias/{id}/estado',   [AdminCategoriaController::class, 'estado'], $admin);
$router->post('/admin/categorias/{id}/eliminar', [AdminCategoriaController::class, 'eliminar'], $admin);

$router->get('/admin/configuracion',             [AdminConfigController::class, 'index'], $admin);
$router->post('/admin/configuracion',            [AdminConfigController::class, 'guardar'], $admin);

// Promociones, empleados, PQR y reportes
$router->get('/admin/promociones',                [AdminPromocionController::class, 'index'], $admin);
$router->get('/admin/promociones/nuevo',          [AdminPromocionController::class, 'nuevo'], $admin);
$router->post('/admin/promociones',               [AdminPromocionController::class, 'crear'], $admin);
$router->get('/admin/promociones/{id}/editar',    [AdminPromocionController::class, 'editar'], $admin);
$router->post('/admin/promociones/{id}',          [AdminPromocionController::class, 'actualizar'], $admin);
$router->post('/admin/promociones/{id}/estado',   [AdminPromocionController::class, 'estado'], $admin);
$router->post('/admin/promociones/{id}/eliminar', [AdminPromocionController::class, 'eliminar'], $admin);

$router->get('/admin/empleados',                  [AdminEmpleadoController::class, 'index'], $admin);
$router->get('/admin/empleados/nuevo',            [AdminEmpleadoController::class, 'nuevo'], $admin);
$router->post('/admin/empleados',                 [AdminEmpleadoController::class, 'crear'], $admin);
$router->get('/admin/empleados/{id}/editar',      [AdminEmpleadoController::class, 'editar'], $admin);
$router->post('/admin/empleados/{id}',            [AdminEmpleadoController::class, 'actualizar'], $admin);
$router->post('/admin/empleados/{id}/estado',     [AdminEmpleadoController::class, 'estado'], $admin);
$router->post('/admin/empleados/{id}/clave',      [AdminEmpleadoController::class, 'clave'], $admin);

$router->get('/admin/pqr',                        [AdminPqrController::class, 'index'], $admin);
$router->get('/admin/pqr/{id}',                   [AdminPqrController::class, 'show'], $admin);
$router->post('/admin/pqr/{id}/responder',        [AdminPqrController::class, 'responder'], $admin);
$router->post('/admin/pqr/{id}/cerrar',           [AdminPqrController::class, 'cerrar'], $admin);

$router->get('/admin/reportes',                   [AdminReporteController::class, 'index'], $admin);
$router->get('/admin/reportes/csv',               [AdminReporteController::class, 'csv'], $admin);
