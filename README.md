# ✿ Mundo Ilusión — tienda de regalos (PHP + MVC + MySQL)

Aplicación web de proyecto de aula. **PHP 8.1+ · MVC · PDO · MySQL/MariaDB · Bootstrap 5 · Wompi**.
Todo (Bootstrap, iconos, PHPMailer) viene **dentro del proyecto**: no necesitas Composer ni npm.

---

## 1. Qué debes instalar (una sola vez)

| Programa | Para qué | Dónde |
|---|---|---|
| **XAMPP** (PHP 8.2 + Apache + MySQL) | Ejecuta la página y la base de datos en tu PC | https://www.apachefriends.org |
| **Visual Studio Code** | Editor de código | https://code.visualstudio.com |
| Extensiones de VS Code | *PHP Intelephense*, *GitLens* | desde el panel de extensiones |
| **Git** | Control de versiones | https://git-scm.com/downloads |
| Cuenta en **GitHub** | Guardar el código y trabajar en equipo | https://github.com |
| **ngrok** (más adelante) | Probar los eventos de Wompi desde tu PC | https://ngrok.com |
| Cuenta **Wompi** sandbox (más adelante) | Pagos de prueba | https://comercios.wompi.co |

## 2. Ponerlo a funcionar (10 minutos)

1. Descomprime este proyecto y copia la carpeta **`mundo-ilusion`** dentro de `C:\xampp\htdocs\`.
2. Abre **XAMPP Control Panel** → pulsa **Start** en **Apache** y en **MySQL**.
3. Abre http://localhost/phpmyadmin (así se administra MySQL con una página web):
   1. Pestaña **Importar** → *Seleccionar archivo* → `database/schema.sql` → **Importar** (crea la base `mundo_ilusion` con sus 18 tablas).
   2. Otra vez **Importar** → `database/seed.sql` (productos, categorías y usuarios de demostración).
4. Revisa el archivo **`.env`** (ya viene listo para XAMPP: usuario `root`, sin contraseña).
5. Abre **http://localhost/mundo-ilusion/** 🎉

### Usuarios de demostración
| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | administrador@mundoilusion.test | `Admin123*` |
| Empleado | empleado@mundoilusion.test | `Empleado123*` |
| Cliente | cliente@mundoilusion.test | `Cliente123*` |

6. **Verifica que todo quedó bien conectado:** abre **http://localhost/mundo-ilusion/diagnostico** (solo funciona con `APP_ENV=local`). Debe decir ✅ y mostrar *18 de 18 tablas* y los datos de demostración.

> Si algo falla: en XAMPP abre `php.ini` (botón *Config* de Apache) y confirma que estén activas (sin `;` delante) las extensiones `pdo_mysql`, `mbstring`, `curl`, `fileinfo` y `openssl`. Reinicia Apache.

## 3. ¿Cómo se conecta PHP con MySQL?
1. `.env` guarda los datos de conexión (servidor, base, usuario, clave). **Es el único sitio donde se escriben.**
2. `app/core/Database.php` abre la conexión con **PDO** una sola vez.
3. Los **Modelos** (`app/models/`) hacen las consultas SQL con parámetros (`?`), lo que evita la inyección SQL.
4. Los **Controladores** llaman a los modelos y le pasan los datos a las **Vistas**.

## 4. Estructura MVC
```
mundo-ilusion/
├── public/            ← lo único visible desde el navegador (index.php, css, js, img, uploads, assets)
├── app/
│   ├── core/          ← Router, Controller base, Database, Auth, Session, Csrf, Validator, Mailer, Wompi, Upload, Chatbot
│   ├── models/        ← consultas a MySQL (Usuario, Producto, Carrito, Pedido, Pago, Pqr, ReporteVentas…)
│   ├── controllers/   ← reciben la petición, validan y deciden qué vista mostrar
│   ├── views/         ← HTML + Bootstrap (layouts, catalogo, carrito, pedidos, admin…)
│   ├── helpers/       ← funciones cortas: e(), url(), cop()…
│   └── libs/PHPMailer ← envío de correos (incluido)
├── routes/web.php     ← mapa de URLs → controlador
├── config/config.php  ← lee .env
├── docs/ESTRUCTURA.md ← guía completa de cada archivo
├── database/          ← schema.sql (tablas) y seed.sql (datos demo)
├── storage/           ← correos de prueba (mail/), errores (logs/), respaldos (backups/) y cupo del chatbot (cache/)
└── cli/               ← scripts de consola
```

## 4.1 Datos reales cargados (del catálogo de la tienda)
`database/seed.sql` trae el catálogo real de **Mundo Ilusión**: **20 productos** (12 desayunos sorpresa, 4 bandejas, 4 cajitas de cartón) con su *Diseño*, *Menú*, precio y fotos, más **7 adicionales** (globos, rosas eternas, cervezas Coronita…) que aparecen como "Agrega un detalle extra".
- **Logo:** `public/img/logo.png` (insignia redonda) y `public/img/logo-wordmark.png` (nombre con mascota, fondo transparente). Si la agencia entrega los archivos finales, **reemplaza esos dos archivos con el mismo nombre** y listo: la tienda, el panel, el favicon y el pie de página se actualizan solos.
- **Datos de la tienda** (WhatsApp, horarios, Instagram, correo…): entra como administrador → **Configuración**. Lo que dejes vacío no se muestra; si pones el WhatsApp aparece el botón flotante.
- **Pendiente de confirmar con la dueña:** (1) los *Desayunos temáticos* no tienen precio en el catálogo, por eso salen como "Pedir cotización"; (2) hay dos productos llamados "Bandeja de amor" ($50.000 y $65.000): el segundo quedó como **"Bandeja de amor Plus"**; (3) las fotos del PDF tienen poca resolución: se pueden reemplazar desde el panel (Productos → Editar → Imágenes); (4) las políticas de entrega, cambios y datos son un borrador.

## 5. Qué ya funciona
- Inicio, catálogo (búsqueda, filtro por categoría/ocasión, orden, paginación), detalle con galería, promociones, información de la tienda.
- Registro (con política de datos), inicio/cierre de sesión, bloqueo tras 5 intentos, recuperar contraseña por correo, perfil, cambio de contraseña y direcciones.
- Carrito en base de datos con descuentos vigentes, checkout (domicilio/recogida, fecha con anticipación mínima, dedicatoria), pedido en una **transacción**.
- Pago con **Wompi** (checkout + webhook firmado + reconciliación) y botón **"Simular pago"** para probar sin llaves (solo con `APP_ENV=local`).
- Mis pedidos, seguimiento, cancelación, expiración automática de pedidos sin pagar, PQR del cliente.
- Panel (administrador): resumen, **pedidos** (cambio de estado con historial y devolución), **productos** (CRUD + imágenes), **categorías**, **promociones** (descuento por porcentaje, fechas y productos; el precio con descuento sale en catálogo, carrito y pedido, y el pedido guarda el precio histórico), **empleados** (alta con contraseña temporal, activar/desactivar, restablecer clave), **PQR** (responder: le llega al cliente por correo), **reportes de ventas** (por fechas, más vendidos, por modalidad, descarga CSV para Excel) y **configuración de la tienda**. El **empleado** solo ve y gestiona pedidos.
- **Asistente virtual "Ilu"** (chatbot) en toda la tienda: ver sección 7.1.
- **Modo nocturno**: botón 🌙/☀️ en la barra superior de la tienda y del panel. Cambia toda la aplicación (incluido el chat), recuerda tu elección y, si nunca la has elegido, sigue el tema de tu sistema operativo.
- Extras sugeridos en cada producto y en el carrito, aviso de licor (solo mayores de 18), botón de WhatsApp, portada con fotos reales y desayunos temáticos.
- Seguridad: PDO con parámetros, `password_hash`, CSRF, escape XSS, roles, sesión con expiración, bloqueo por intentos, subida de imágenes validada, CSV protegido contra inyección de fórmulas, clave del chatbot solo en el servidor.
- **Respaldo de la base de datos:** `C:\xampp\php\php.exe cli\respaldo.php` crea `storage/backups/AAAA-MM-DD_HHMM.sql` (conserva los 14 últimos; no se suben a GitHub).

**Pendiente:** probar Wompi con tu cuenta sandbox, correo real (SMTP), confirmar precios/fotos con la dueña, reemplazar el logo por el final y publicar en un hosting con HTTPS.

## 6. Git y GitHub (trabajo en equipo)
> 🔒 **Antes de subir nada:** el archivo `.env` (contraseñas y llaves) **no debe subirse**. Ya está en `.gitignore`, pero comprueba con `git check-ignore -v .env` (debe responder con la línea del `.gitignore`) y con `git status` (no debe aparecer `.env`). Más detalle en `docs/ESTRUCTURA.md` (sección 14).

**Una persona** crea el repositorio:
1. En GitHub: **New repository** → nombre `mundo-ilusion` → *Private* → **sin** README ni .gitignore.
2. En la carpeta del proyecto (terminal de VS Code, `Ctrl+ñ`):
```bash
git init
git config user.name "Tu Nombre"
git config user.email "tu@correo.com"
git add .
git commit -m "Base del proyecto Mundo Ilusión"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/mundo-ilusion.git
git push -u origin main
```
3. En GitHub → *Settings → Collaborators* → invita a tus compañeros.

**Los demás** clonan: `git clone https://github.com/TU_USUARIO/mundo-ilusion.git` dentro de `C:\xampp\htdocs\`, copian `.env.example` como `.env` e importan la base de datos (paso 2.3).

**Flujo diario recomendado** (cada quien en su rama):
```bash
git pull                              # traer lo último
git checkout -b feature/promociones   # rama nueva para tu tarea
# ...trabajas...
git add .  &&  git commit -m "CRUD de promociones"
git push -u origin feature/promociones   # y en GitHub abres un Pull Request
```
`.env` **nunca** se sube (ya está en `.gitignore`): ahí viven las claves.
**Cambios en la base de datos:** si cambias una tabla, guarda el cambio en un archivo nuevo en `database/` (por ejemplo `cambio-02-xxx.sql`) y avisa al equipo.

## 7. Wompi (pagos) — paso a paso
1. Crea tu cuenta en https://comercios.wompi.co y entra en modo **pruebas / sandbox**.
2. En *Desarrolladores* copia: **llave pública** (`pub_test_…`), **secreto de integridad** (`test_integrity_…`) y **secreto de eventos** (`test_events_…`) y pégalos en `.env`.
3. Para que Wompi avise a tu PC: instala **ngrok** y ejecuta `ngrok http 80`. Copia la URL `https://xxxx.ngrok-free.app` y en Wompi (*Desarrolladores → URL de eventos*) pon:
   `https://xxxx.ngrok-free.app/mundo-ilusion/public/webhooks/wompi`
4. Haz un pedido y pulsa **Pagar con Wompi**. Datos de prueba del sandbox: tarjeta aprobada `4242 4242 4242 4242` o `4111 1111 1111 1111` (cualquier fecha futura y CVC); Nequi aprobado `3991111111`, rechazado `3992222222`; en PSE elige el banco de prueba que aprueba o el que rechaza.
5. Aunque no uses ngrok, al volver del checkout el sistema consulta la transacción a Wompi y actualiza el pedido.

## 7.1 Chatbot "Ilu"
Aparece como un botón rosa "Pregúntale a Ilu" en toda la tienda. **Funciona sin configurar nada**: responde por reglas con datos reales (productos, precios, promociones, horarios, pagos, entregas, cómo pedir).
Para que responda con **IA** (más natural):
1. Crea una cuenta gratis en https://console.groq.com y genera una clave en *API Keys*.
2. En `.env` pega la clave: `CHATBOT_API_KEY=gsk_...` (reinicia Apache o recarga la página).
3. Opcional: `CHATBOT_MODEL` (los nombres cambian con el tiempo; si falla, revisa la lista de modelos en tu consola), `CHATBOT_DAILY_LIMIT` (tope de respuestas con IA por día; al agotarse sigue por reglas) y `CHATBOT_API_URL` si usas otro proveedor compatible con OpenAI.
4. Para apagarlo del todo: `CHATBOT_ENABLED=false`.
Seguridad: la clave **solo vive en el servidor**; el chat tiene token CSRF, máximo 500 caracteres por mensaje y 20 mensajes cada 10 minutos por visitante; la IA solo recibe el catálogo y las políticas de la tienda. Si la IA falla, el chat sigue por reglas.

## 8. Correos reales (Gmail)
Por defecto los correos **no se envían**: se guardan como archivos en `storage/mail/` (ábrelos con el navegador). Para enviarlos de verdad:
1. En tu cuenta Google activa la verificación en 2 pasos y crea una **contraseña de aplicación**.
2. En `.env`: `MAIL_ENABLED=true`, `MAIL_USER=tucorreo@gmail.com`, `MAIL_PASS=<contraseña de aplicación>`, `MAIL_FROM=tucorreo@gmail.com`.

## 9. Producción (cuando toque presentar en línea)
Hosting con PHP 8.1+ y MySQL (Hostinger, cPanel…): sube el proyecto, apunta el dominio a la carpeta **`public/`**, importa `schema.sql`, crea el `.env` con `APP_ENV=production`, tus datos de BD y las llaves **de producción** de Wompi, y cambia las contraseñas de los usuarios demo.

## 10. Problemas frecuentes
| Síntoma | Solución |
|---|---|
| `Access denied for user 'root'` | Revisa `DB_USER` y `DB_PASS` en `.env` |
| `Unknown database 'mundo_ilusion'` | Falta importar `schema.sql` |
| Página en blanco / error 500 | Mira `storage/logs/php-errors.log` |
| Las imágenes no suben | Revisa que `public/uploads/productos/` exista y permita escribir |
| `419 La página expiró` | Recarga el formulario (el token de seguridad venció) |
