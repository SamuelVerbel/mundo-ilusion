# Mundo Ilusión — Guía de la estructura del proyecto

Esta guía explica **qué hay en cada carpeta y archivo, para qué sirve y qué usa**. Léela de arriba hacia abajo la primera vez; después úsala como mapa de consulta.

---

## 1. Cómo viaja una petición (la idea más importante)

Cuando alguien abre `http://localhost/mundo-ilusion/catalogo?q=rosas`:

```
Navegador
  └─► Apache (XAMPP) ── .htaccess (raíz) manda todo a /public ── public/.htaccess manda lo que no sea un archivo real a index.php
        └─► public/index.php        (único punto de entrada = "front controller")
              1. config/config.php  → lee .env, define BASE_URL, zona horaria y carga app/helpers/functions.php
              2. autoload           → cuando PHP necesita una clase (p. ej. Producto) la busca sola en app/core, app/models, app/controllers
              3. Session::start()   → abre la sesión segura
              4. routes/web.php     → registra "URL + método → Controlador::acción"
              5. Router::dispatch() → busca la ruta, valida CSRF (si es POST) y rol (si la ruta lo exige)
                    └─► Controlador (ej. CatalogoController::index)
                          ├─► Modelos (Producto::buscar) → consultan MySQL con PDO (Database)
                          └─► Vista (views/catalogo/index.php) dentro del layout (views/layouts/main.php)
                                └─► HTML + Bootstrap → vuelve al navegador
```

**Regla MVC del proyecto:** el **Modelo** habla con MySQL, la **Vista** solo dibuja HTML, el **Controlador** valida y decide. Nunca hay SQL en una vista ni HTML en un modelo.

**Convención clave del autoload:** el nombre del archivo **debe ser igual al nombre de la clase** (`Producto.php` contiene `class Producto`). Si lo rompes, PHP dirá "Class not found".

---

## 2. Árbol del proyecto

```
mundo-ilusion/
├── .env                      ← 🔒 SECRETOS locales (NO se sube a GitHub)
├── .env.example              ← plantilla sin secretos (SÍ se sube)
├── .gitignore                ← lista de lo que Git debe ignorar
├── .gitattributes            ← normaliza saltos de línea Windows/Linux
├── .htaccess                 ← permite abrir /mundo-ilusion/ sin escribir /public
├── README.md                 ← instalación rápida
├── config/
│   └── config.php
├── routes/
│   └── web.php
├── public/                   ← lo único que el navegador puede ver
│   ├── index.php
│   ├── .htaccess
│   ├── css/app.css
│   ├── js/app.js
│   ├── img/                  logo.png, logo-wordmark.png, favicon-*.png, tienda.jpg, productos/ (fotos reales y extras), tematicos/
│   ├── uploads/productos/    ← fotos que sube el administrador (NO se suben a GitHub)
│   └── assets/               ← Bootstrap 5.3.3 y Bootstrap Icons 1.11.3 (copias locales)
├── app/
│   ├── core/                 ← "motor" del proyecto (11 clases)
│   ├── helpers/functions.php
│   ├── models/               ← consultas a MySQL (13 clases)
│   ├── controllers/          ← lógica de cada pantalla (20 clases)
│   ├── views/                ← HTML (layouts, partials y una carpeta por módulo)
│   └── libs/PHPMailer/       ← librería de correo (de terceros, incluida)
├── database/
│   ├── schema.sql            ← crea la BD y las 18 tablas
│   └── seed.sql              ← datos de demostración y usuarios demo
├── storage/
│   ├── mail/                 ← correos de prueba guardados como .html (NO se suben)
│   ├── logs/                 ← errores de PHP (NO se suben)
│   ├── backups/              ← respaldos .sql de la base (NO se suben)
│   └── cache/                ← cupo diario del chatbot (NO se sube)
├── cli/                      ← expirar_pedidos.php y respaldo.php
└── docs/ESTRUCTURA.md        ← este archivo
```

---

## 3. Raíz del proyecto

| Archivo | Qué contiene | Para qué sirve |
|---|---|---|
| `.env` | Pares `CLAVE=valor`: datos de MySQL, correo y llaves de Wompi | Es el **único** lugar con contraseñas y secretos. Cada computador tiene el suyo. **Nunca** se sube. |
| `.env.example` | Lo mismo que `.env` pero con valores vacíos o de ejemplo | Plantilla: quien clone el repositorio la copia como `.env` y la completa. |
| `.gitignore` | Patrones de archivos | Le dice a Git qué NO versionar: `.env`, correos y logs generados, fotos subidas, copias de BD, carpetas del editor. |
| `.gitattributes` | Reglas de saltos de línea | Evita que Windows y otros sistemas "cambien" todos los archivos al commitear. |
| `.htaccess` | 2 reglas de reescritura de Apache | Hace que `localhost/mundo-ilusion/` funcione sin escribir `/public`. Requiere `mod_rewrite` (activo en XAMPP). |

### Variables del `.env`
| Variable | Uso |
|---|---|
| `APP_ENV` | `local` muestra errores y habilita "Simular pago" y `/diagnostico`. En producción: `production`. |
| `APP_URL` | Opcional: URL pública fija (para correos y Wompi). Vacío = se detecta sola. |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | Conexión a MySQL (XAMPP: `127.0.0.1`, `3306`, `mundo_ilusion`, `root`, vacío). |
| `MAIL_ENABLED`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM`, `MAIL_FROM_NAME` | Correo SMTP. Con `MAIL_ENABLED=false` los correos se guardan en `storage/mail/`. |
| `WOMPI_ENV`, `WOMPI_PUBLIC_KEY`, `WOMPI_INTEGRITY_SECRET`, `WOMPI_EVENTS_SECRET` | Pagos. Los dos `SECRET` son **secretos**. |
| `CHATBOT_ENABLED`, `CHATBOT_API_KEY`, `CHATBOT_API_URL`, `CHATBOT_MODEL`, `CHATBOT_MAX_TOKENS`, `CHATBOT_DAILY_LIMIT` | Asistente "Ilu". Sin `API_KEY` responde por reglas; con clave (Groq u otra API compatible con OpenAI) responde con IA. La clave es un **secreto**. |

---

## 4. `config/` y `routes/`

- **`config/config.php`** — Se carga una sola vez desde `public/index.php`. Define `ROOT_PATH` (carpeta raíz), **lee `.env`** (función `env('CLAVE', 'defecto')`), fija zona horaria `America/Bogota`, muestra/oculta errores según `APP_ENV`, calcula `BASE_URL` (la carpeta desde la que se sirve el sitio) y carga `app/helpers/functions.php`.
- **`routes/web.php`** — La lista de todas las URLs. Cada línea: `$router->get('/catalogo', [CatalogoController::class, 'index'])`. Una ruta puede llevar opciones: `['roles' => ['cliente']]` (exige sesión y ese rol) o `['csrf' => false]` (solo el webhook de Wompi). Para crear una página nueva **siempre** empiezas aquí.

---

## 5. `public/` (la parte visible)

| Archivo | Qué hace |
|---|---|
| `index.php` | Punto de entrada: carga config, registra el **autoload**, inicia sesión, define el manejador de errores 500 y ejecuta el router. |
| `.htaccess` | Si la URL no es un archivo/carpeta real, la manda a `index.php`; desactiva el listado de carpetas. |
| `css/app.css` | Estilos propios (paleta rosa `#E0457B`, lavanda `#8E6BBE`, dorado `#D9A441`, tarjetas, panel admin). Se apoya en Bootstrap. |
| `js/app.js` | Galería de miniaturas, campos de dirección del checkout, **modo nocturno** (botón, `localStorage`, tema del sistema) y el **chat de Ilu** (escapa todo el texto antes de darle formato: sin XSS). |
| `img/sin-imagen.svg` | Imagen de reemplazo cuando un producto no tiene foto. |
| `img/logo.png`, `img/logo-wordmark.png` | Logo de la marca (insignia redonda y nombre con mascota). **Reemplázalos por los archivos finales de la agencia con el mismo nombre.** |
| `img/favicon-32.png`, `favicon-64.png`, `apple-touch-icon.png` | Iconos de la pestaña del navegador (salen del logo). |
| `img/tienda.jpg` | Foto de la tienda (portada y página "La tienda"). |
| `img/tematicos/t1..t6.png` | Recortes con fondo transparente de los desayunos temáticos (portada). |
| `img/productos/*.jpg` | Fotos reales del catálogo (las referencia `seed.sql`). `extra-*.svg` son los iconos de los adicionales. |
| `uploads/productos/` | Fotos reales que sube el administrador (nombre aleatorio). Solo se versiona el `.gitkeep`. |
| `assets/bootstrap/…` | Bootstrap 5.3.3 (CSS y JS) **local**: la página funciona sin internet. |
| `assets/bootstrap-icons/…` | Iconos `bi bi-…` (CSS y fuentes). |

> Dependencia externa opcional: las **tipografías** (Playfair Display y Nunito) se cargan desde Google Fonts. Sin internet se ve igual, con tipografías del sistema.

---

## 6. `app/core/` — el motor

| Clase | Qué contiene | Quién la usa |
|---|---|---|
| `Database` | Conexión **PDO** única a MySQL y atajos: `all()` (varias filas), `one()` (una fila), `val()` (un valor), `exec()` (UPDATE/DELETE), `insert()` (devuelve el id), `begin/commit/rollback` (transacciones). Todas usan parámetros `?`. | Todos los modelos |
| `Router` | Guarda las rutas, hace coincidir URL + método, valida **CSRF** en POST y **roles**; si nada coincide → 404. | `public/index.php`, `routes/web.php` |
| `Controller` | Clase padre de los controladores: `view()` (pinta una vista dentro de un layout), `back()` (vuelve con los datos escritos y un mensaje de error), `post()`, `renderError()`. | Todos los controladores |
| `Session` | Sesión segura (cookie HttpOnly, SameSite), **expira a los 30 min** de inactividad, mensajes *flash* y recordar datos de formularios. | `index.php`, layouts |
| `Csrf` | Genera y valida el token secreto de cada formulario POST. | `Router`, `csrf_field()` |
| `Auth` | Quién está logueado (`user()`, `id()`, `role()`, `is()`), `login()` (regenera la sesión), `logout()`. | Router, controladores, vistas |
| `Validator` | Reglas encadenables: `required`, `email`, `min`, `max`, `regex`, `in`, `custom`, `password`. | Controladores |
| `Mailer` | `send($para, $asunto, $html)`: usa SMTP con PHPMailer si `MAIL_ENABLED=true`; si no, guarda un `.html` en `storage/mail/`. | Auth, Notificacion |
| `Wompi` | Firma de integridad, URL del checkout, verificación del checksum de eventos y consulta de transacciones. | PedidoController, WebhookController |
| `Chatbot` | Asistente "Ilu": `responder()` usa IA si hay clave y cupo diario (prompt con el catálogo real) y, si no, `basico()` (reglas con datos de la BD). | ChatbotController |
| `Upload` | Subida segura de imágenes: valida el tipo real (JPG/PNG/WEBP), máx. 2 MB, nombre aleatorio. | AdminProductoController |

### `app/helpers/functions.php` — funciones cortas disponibles en todas partes
`e()` (escapa HTML: **anti-XSS**, úsala siempre al imprimir datos) · `descripcion_html()` (convierte "Diseño:" y "- punto" en títulos y listas) · `resumen_menu()` · `contiene_licor()` · `whatsapp_url()` · `url('ruta')` · `asset('css/app.css')` · `absolute_url()` · `cop(85000)` → `$85.000` · `fecha()` · `old('campo')` (recuerda lo escrito) · `csrf_field()` · `redirect()` · `abort(404)` · `flash()` · `conf('clave')` (lee `configuracion`) · `img_producto()` · `badge_pedido()` / `badge_pago()` (etiquetas de color) y la constante `ESTADOS_PEDIDO`.

---

## 7. `app/models/` — consultas a MySQL

| Modelo | Tablas que toca | Qué hace |
|---|---|---|
| `Usuario` | `usuario` | Gestión de empleados (listar, editar, activar, contraseña temporal). Buscar por id/correo, crear (con `password_hash`), actualizar perfil, cambiar clave, contar fallos y **bloquear 15 min tras 5 intentos**. |
| `Direccion` | `direccion` | Direcciones del cliente. No se borran: se **desactivan** (los pedidos viejos conservan su dirección). |
| `PasswordReset` | `password_reset` | Token de recuperación de un solo uso (vence en 1 hora; solo se guarda su hash). |
| `Categoria` | `categoria`, `producto_categoria` | Categorías, ocasiones y **adicionales** (`tipo`): listar con foto y conteo, crear, editar, activar/desactivar, eliminar si no tiene productos. |
| `Producto` | `producto`, `producto_imagen`, `producto_categoria` | Catálogo con búsqueda/filtros/paginación (sin los extras), detalle, `destacados()` (rota cada día), `adicionales()`, **descuento vigente** en SQL, CRUD de administración, imágenes. |
| `Promocion` | `promocion`, `producto_promocion` | Vigentes (para la tienda) y CRUD del panel: guardar en transacción con sus productos, activar/desactivar, eliminar. |
| `Pqr` | `pqr` | Crear, listar con filtros, responder (envía correo al cliente) y cerrar. |
| `ReporteVentas` | `pedido`, `detalle_pedido` | Totales, ventas por día, más vendidos y por modalidad (solo pedidos pagados) y descarga CSV. |
| `Carrito` | `carrito`, `detalle_carrito` | Carrito activo del cliente: agregar, cambiar cantidad, eliminar, totales con descuento. |
| `Pedido` | `pedido`, `detalle_pedido`, `pedido_estado_historial` | **Crea el pedido desde el carrito en una transacción**, ciclo de estados permitido, cancelación, expiración automática, listados y estadísticas. |
| `Pago` | `pago`, `evento_pago` | Intentos de pago (referencia única), `aplicarResultado()` (idempotente), eventos de Wompi, devolución. |
| `Notificacion` | (usa Pedido y Mailer) | Correo de confirmación al cliente y aviso al negocio cuando un pago se aprueba. |
| `Configuracion` | `configuracion` | Parámetros (anticipación, expiración) e información de la tienda. |

---

## 8. `app/controllers/` — una clase por módulo

| Controlador | Rutas que atiende | Resumen |
|---|---|---|
| `HomeController` | `/` | Portada: destacados, categorías, promoción. |
| `CatalogoController` | `/catalogo`, `/producto/{id}`, `/promociones` | Catálogo con filtros, detalle y promociones. |
| `TiendaController` | `/tienda` | Información y políticas de la tienda. |
| `AuthController` | `/login`, `/registro`, `/logout`, `/recuperar`, `/restablecer/{token}` | Registro, login (mensaje genérico de error, bloqueo), recuperación de contraseña. |
| `PerfilController` | `/perfil…` | Datos personales, cambio de clave y direcciones. |
| `CarritoController` | `/carrito…` | Ver, agregar (si no hay sesión, lo manda a iniciar y luego regresa), actualizar, eliminar. |
| `PedidoController` | `/checkout`, `/pedidos…`, `/pago/retorno` | Checkout, mis pedidos, cancelar, pagar con Wompi, retorno de Wompi y "simular pago" (solo local). |
| `PqrController` | `/pqr` | El cliente envía y ve sus PQR. |
| `ChatbotController` | `/chatbot/mensaje`, `/chatbot/reiniciar` | JSON del chat: CSRF, límite 20 mensajes/10 min por sesión, historial en sesión. |
| `WebhookController` | `/webhooks/wompi` | Recibe eventos de Wompi: valida firma, evita duplicados, aplica el pago. Sin sesión ni CSRF (se protege con la firma). |
| `DiagnosticoController` | `/diagnostico` | Revisa PHP, MySQL y carpetas. Solo con `APP_ENV=local`. |
| `AdminController` | `/admin` | Resumen del panel. Base de los demás controladores admin. |
| `AdminPedidoController` | `/admin/pedidos…` | Listar, ver, cambiar estado (empleado y administrador) y registrar devolución (solo administrador). |
| `AdminProductoController` | `/admin/productos…` | CRUD de productos e imágenes (solo administrador). |
| `AdminCategoriaController` | `/admin/categorias…` | CRUD de categorías, ocasiones y adicionales (solo administrador). |
| `AdminPromocionController` | `/admin/promociones…` | CRUD de promociones (validación de % y fechas, productos válidos). |
| `AdminEmpleadoController` | `/admin/empleados…` | Alta con contraseña temporal (se muestra una vez y llega por correo), editar, activar/desactivar, nueva clave. Solo opera sobre rol *empleado*. |
| `AdminPqrController` | `/admin/pqr…` | Lista con filtros, responder y cerrar. |
| `AdminReporteController` | `/admin/reportes`, `/admin/reportes/csv` | Reporte por rango de fechas y descarga CSV. |
| `AdminConfigController` | `/admin/configuracion` | Edita datos de la tienda, parámetros (anticipación, expiración) y políticas (solo administrador). |

---

## 9. `app/views/` — el HTML

- **`layouts/main.php`**: marco de la tienda (navbar con categorías, carrito con contador, menú de usuario, mensajes flash, footer con datos de `configuracion`). **`layouts/admin.php`**: marco del panel (menú lateral con contador de PQR pendientes; el empleado solo ve Resumen y Pedidos). Ambos cargan `partials/tema_head.php` (aplica el tema guardado antes de pintar, sin parpadeo) y `partials/tema_toggle.php` (botón 🌙/☀️).
- **`partials/`**: piezas reutilizables → `producto_card.php` (tarjeta de producto), `extras.php` (bloque "Agrega un detalle extra"), `paginacion.php`, `chatbot.php` (burbuja de Ilu), `tema_head.php` y `tema_toggle.php`.
- **Carpetas por módulo**: `home/`, `catalogo/` (index, show, promociones), `tienda/`, `auth/` (login, registro, olvido, reset), `perfil/`, `carrito/`, `pedidos/` (checkout, index, show), `pqr/`, `errors/error.php` (404, 403, 419, 500) y `admin/` (dashboard, pedidos, productos, categorias, config, promociones, empleados, pqr, reportes).
- Cada vista recibe variables del controlador (por ejemplo `$p`, `$items`) y **siempre escapa con `e()`**. Los formularios incluyen `<?= csrf_field() ?>`.

---

## 10. `database/`

- **`schema.sql`** — ⚠️ Empieza con `DROP DATABASE IF EXISTS mundo_ilusion`: **borra todo** y vuelve a crearlo. Impórtalo **una sola vez**. Crea las 18 tablas con llaves primarias y foráneas, índices y restricciones (`CHECK`).
- **`seed.sql`** — Catálogo **real** (20 productos + 7 adicionales con fotos), 3 categorías + "Adicionales", parámetros de la tienda y 3 usuarios de prueba (solo guarda los *hashes*, no las contraseñas). Sin promociones todavía.

> `categoria.tipo` ahora acepta tres valores: `categoria`, `ocasion` y `adicional` (los extras que se ofrecen junto a cada producto y no salen en el catálogo).

**Las 18 tablas:** `usuario`, `direccion`, `password_reset`, `categoria`, `producto`, `producto_imagen`, `producto_categoria`*, `promocion`, `producto_promocion`*, `carrito`, `detalle_carrito`*, `pedido`, `detalle_pedido`*, `pedido_estado_historial`, `pago`, `evento_pago`, `pqr`, `configuracion` (*tablas intermedias de relaciones N:M).

---

## 11. `app/libs/PHPMailer/` y `cli/`

- **PHPMailer 6.9.3** (3 archivos + licencia). Es código de terceros: **no lo edites**. `Mailer` lo carga (`require_once`) solo cuando `MAIL_ENABLED=true`.
- **`cli/respaldo.php`** — Crea `storage/backups/AAAA-MM-DD_HHMM.sql` con `mysqldump` (detecta el de XAMPP) y conserva los 14 más recientes. Ejecuta: `C:\xampp\php\php.exe cli\respaldo.php` (o prográmalo a diario).
- **`cli/expirar_pedidos.php`** — Cancela pedidos sin pagar vencidos. La web ya lo hace sola al abrir pedidos; el script sirve para programarlo (Programador de tareas / cron).

---

## 12. Dependencias y "importaciones" (resumen)

| Qué | Versión | Dónde está | Cómo se carga |
|---|---|---|---|
| PHP | 8.1 o superior | XAMPP | — |
| Extensiones PHP | `pdo_mysql`, `mbstring`, `curl`, `fileinfo`, `openssl` | `php.ini` | Deben estar activas |
| MySQL / MariaDB | MySQL 8 / MariaDB 10.4+ | XAMPP | Conexión PDO (`Database`) |
| Bootstrap | 5.3.3 | `public/assets/bootstrap/` | `<link>` y `<script>` en los layouts |
| Bootstrap Icons | 1.11.3 | `public/assets/bootstrap-icons/` | `<link>` en los layouts |
| PHPMailer | 6.9.3 | `app/libs/PHPMailer/` | `require_once` dentro de `Mailer` |
| Google Fonts | — | Internet | `<link>` (opcional) |
| Wompi | API v1 | Servicio externo | `Wompi` usa `curl` y redirige al checkout |

No hay Composer, npm ni carpeta `vendor/`: **todo está dentro del repositorio**.

Cómo se "importan" archivos PHP: (1) `config.php` hace `require` de `functions.php`; (2) `index.php` hace `require` de `web.php`; (3) las **clases** se cargan solas por el autoload según su nombre; (4) `Controller::view()` hace `require` de la vista y luego del layout; (5) las vistas hacen `require` de los parciales.

---

## 12.1 Modo nocturno (cómo funciona)
Bootstrap 5.3 cambia de tema con el atributo `data-bs-theme="dark"` en `<html>`. `tema_head.php` lo pone antes de pintar (lee `localStorage` o el tema del sistema); `app.js` maneja el botón. En `app.css` los colores de la marca son variables (`--mi-bg`, `--mi-surface`, `--mi-text`, `--mi-primary-text`…) y el bloque `[data-bs-theme=dark]` las redefine: **si añades una pantalla, usa esas variables (no `#fff` ni colores fijos) y se adaptará sola.**

## 13. Recetas rápidas

**Agregar una página nueva:** 1) ruta en `routes/web.php` → 2) método en un controlador (o uno nuevo `NombreController.php`) → 3) vista en `app/views/modulo/` → 4) si necesita datos, un método en un modelo.
**Agregar una tabla/columna:** crea un archivo nuevo `database/cambio-NN-descripcion.sql` con `ALTER TABLE …`, ejecútalo en phpMyAdmin, **actualiza `schema.sql`** y avisa al equipo. Nunca reimportes `schema.sql` en una base con datos.
**Agregar una clave al `.env`:** agrégala también (vacía) a `.env.example` y léela con `env('CLAVE')`.

---

## 14. Archivos delicados (resumen para GitHub)

| Nivel | Archivo/carpeta | Por qué |
|---|---|---|
| 🔴 Nunca subir | `.env` | Contraseña de MySQL, de correo, secretos de Wompi y la clave del chatbot |
| 🔴 Nunca subir | `storage/backups/*` | Copias completas de la base (datos de clientes) |
| 🔴 Nunca subir | `storage/mail/*` | Contienen enlaces de recuperación de contraseña y datos de clientes |
| 🔴 Nunca subir | `storage/logs/*` | Pueden registrar rutas internas y datos de errores |
| 🟠 No subir | `public/uploads/productos/*` | Fotos subidas en uso real; son datos, no código |
| 🟠 No subir | copias de la base con datos reales (`*.sql.bak`, `backup*.sql`) | Datos personales de clientes |
| 🟡 Cuidado | `database/seed.sql` y README | Usuarios demo: úsalos solo en desarrollo; **cámbialos antes de publicar en internet** |
| 🟡 Cuidado | `app/libs/PHPMailer/` | Código ajeno: no modificar |
| 🟢 Sí subir | `.env.example`, `.gitignore`, `.gitattributes`, todo `app/`, `config/`, `routes/`, `public/` (menos uploads), `database/schema.sql`, `docs/` | Es el proyecto |
