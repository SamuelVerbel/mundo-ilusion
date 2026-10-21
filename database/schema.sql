-- =====================================================================
--  MUNDO ILUSIÓN · Esquema definitivo (18 tablas)  ·  MySQL 8 / MariaDB 10.4+
--  Ejecutar completo en phpMyAdmin (pestaña Importar) o con: mysql -u root < database/schema.sql
-- =====================================================================
DROP DATABASE IF EXISTS mundo_ilusion;
CREATE DATABASE mundo_ilusion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mundo_ilusion;

-- ---------- USUARIOS (Cliente, Empleado y Administrador son filas de esta tabla; el rol los distingue) ----------
CREATE TABLE usuario (
  id_usuario                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  documento                 VARCHAR(20)  NOT NULL UNIQUE,
  nombre                    VARCHAR(120) NOT NULL,
  correo                    VARCHAR(150) NOT NULL UNIQUE,
  telefono                  VARCHAR(20)  NOT NULL,
  password_hash             VARCHAR(255) NOT NULL,
  rol                       ENUM('cliente','empleado','administrador') NOT NULL DEFAULT 'cliente',
  estado                    ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_aceptacion_politica DATETIME NULL,
  intentos_fallidos         TINYINT UNSIGNED NOT NULL DEFAULT 0,
  bloqueado_hasta           DATETIME NULL,
  fecha_registro            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_acceso             DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE direccion (
  id_direccion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario   INT UNSIGNED NOT NULL,
  direccion    VARCHAR(200) NOT NULL,
  barrio       VARCHAR(100) NOT NULL,
  referencia   VARCHAR(200) NULL,
  estado       ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  CONSTRAINT fk_direccion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  INDEX idx_direccion_usuario (id_usuario)
) ENGINE=InnoDB;

CREATE TABLE password_reset (
  id_reset   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expira_en  DATETIME NOT NULL,
  usado      TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_reset_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- CATÁLOGO ----------
CREATE TABLE categoria (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(80)  NOT NULL UNIQUE,
  descripcion  VARCHAR(255) NULL,
  tipo         ENUM('categoria','ocasion','adicional') NOT NULL DEFAULT 'categoria',   -- 'adicional' = extras que se ofrecen junto a cada producto
  estado       ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE producto (
  id_producto    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(150)  NOT NULL,
  descripcion    TEXT NULL,
  precio         DECIMAL(12,2) NOT NULL,
  disponible     TINYINT(1) NOT NULL DEFAULT 1,           -- disponibilidad simple (sí/no), sin inventario
  estado         ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_producto_precio CHECK (precio >= 0),
  INDEX idx_producto_estado (estado), INDEX idx_producto_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE producto_imagen (
  id_imagen    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_producto  INT UNSIGNED NOT NULL,
  ruta         VARCHAR(255) NOT NULL,
  orden        TINYINT UNSIGNED NOT NULL DEFAULT 0,
  es_principal TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_imagen_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE,
  INDEX idx_imagen_producto (id_producto)
) ENGINE=InnoDB;

-- N:M  Producto <-> Categoria
CREATE TABLE producto_categoria (
  id_producto  INT UNSIGNED NOT NULL,
  id_categoria INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_producto, id_categoria),
  CONSTRAINT fk_pc_producto  FOREIGN KEY (id_producto)  REFERENCES producto(id_producto)   ON DELETE CASCADE,
  CONSTRAINT fk_pc_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE promocion (
  id_promocion         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre               VARCHAR(120) NOT NULL,
  porcentaje_descuento DECIMAL(5,2) NOT NULL,
  fecha_inicio         DATE NOT NULL,
  fecha_fin            DATE NOT NULL,
  estado               ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
  CONSTRAINT chk_promo_pct CHECK (porcentaje_descuento >= 1 AND porcentaje_descuento <= 100),
  CONSTRAINT chk_promo_fechas CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB;

-- N:M  Producto <-> Promocion
CREATE TABLE producto_promocion (
  id_producto  INT UNSIGNED NOT NULL,
  id_promocion INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_producto, id_promocion),
  CONSTRAINT fk_pp_producto  FOREIGN KEY (id_producto)  REFERENCES producto(id_producto)   ON DELETE CASCADE,
  CONSTRAINT fk_pp_promocion FOREIGN KEY (id_promocion) REFERENCES promocion(id_promocion) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- CARRITO ----------
CREATE TABLE carrito (
  id_carrito     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario     INT UNSIGNED NOT NULL,
  estado         ENUM('activo','convertido') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_carrito_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  INDEX idx_carrito_usuario (id_usuario, estado)
) ENGINE=InnoDB;

-- N:M  Carrito <-> Producto (con cantidad)
CREATE TABLE detalle_carrito (
  id_detalle_carrito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_carrito  INT UNSIGNED NOT NULL,
  id_producto INT UNSIGNED NOT NULL,
  cantidad    INT UNSIGNED NOT NULL,
  CONSTRAINT chk_dc_cantidad CHECK (cantidad > 0),
  UNIQUE KEY uq_carrito_producto (id_carrito, id_producto),
  CONSTRAINT fk_dc_carrito  FOREIGN KEY (id_carrito)  REFERENCES carrito(id_carrito)   ON DELETE CASCADE,
  CONSTRAINT fk_dc_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB;

-- ---------- PEDIDOS ----------
CREATE TABLE pedido (
  id_pedido         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario        INT UNSIGNED NOT NULL,
  id_direccion      INT UNSIGNED NULL,
  modalidad_entrega ENUM('domicilio','recogida') NOT NULL,
  fecha_entrega     DATE NOT NULL,
  dedicatoria       VARCHAR(255) NULL,
  observaciones     TEXT NULL,
  subtotal          DECIMAL(12,2) NOT NULL,
  descuento_total   DECIMAL(12,2) NOT NULL DEFAULT 0,
  total             DECIMAL(12,2) NOT NULL,
  estado            ENUM('pendiente_pago','recibido','confirmado','en_preparacion','en_camino','listo_para_recoger','entregado','cancelado') NOT NULL DEFAULT 'pendiente_pago',
  fecha_pedido      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion  DATETIME NOT NULL,
  CONSTRAINT fk_pedido_usuario   FOREIGN KEY (id_usuario)   REFERENCES usuario(id_usuario),
  CONSTRAINT fk_pedido_direccion FOREIGN KEY (id_direccion) REFERENCES direccion(id_direccion),
  CONSTRAINT chk_pedido_direccion CHECK (modalidad_entrega = 'recogida' OR id_direccion IS NOT NULL),
  INDEX idx_pedido_usuario (id_usuario), INDEX idx_pedido_estado (estado), INDEX idx_pedido_fecha (fecha_pedido)
) ENGINE=InnoDB;

-- N:M  Pedido <-> Producto (guarda nombre y precio HISTÓRICOS del momento de la compra)
CREATE TABLE detalle_pedido (
  id_detalle_pedido    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pedido            INT UNSIGNED NOT NULL,
  id_producto          INT UNSIGNED NOT NULL,
  nombre_producto      VARCHAR(150) NOT NULL,
  cantidad             INT UNSIGNED NOT NULL,
  precio_unitario      DECIMAL(12,2) NOT NULL,
  porcentaje_descuento DECIMAL(5,2) NOT NULL DEFAULT 0,
  subtotal             DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_dp_pedido   FOREIGN KEY (id_pedido)   REFERENCES pedido(id_pedido) ON DELETE CASCADE,
  CONSTRAINT fk_dp_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB;

CREATE TABLE pedido_estado_historial (
  id_historial    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pedido       INT UNSIGNED NOT NULL,
  id_usuario      INT UNSIGNED NULL,              -- NULL = cambio hecho por el sistema
  estado_anterior VARCHAR(30) NULL,
  estado_nuevo    VARCHAR(30) NOT NULL,
  fecha_cambio    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  nota            VARCHAR(255) NULL,
  CONSTRAINT fk_hist_pedido  FOREIGN KEY (id_pedido)  REFERENCES pedido(id_pedido) ON DELETE CASCADE,
  CONSTRAINT fk_hist_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- ---------- PAGOS (Wompi) ----------
CREATE TABLE pago (
  id_pago              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pedido            INT UNSIGNED NOT NULL,
  referencia           VARCHAR(80) NOT NULL UNIQUE,
  id_transaccion_wompi VARCHAR(80) NULL,
  metodo_pago          VARCHAR(40) NULL,
  monto                DECIMAL(12,2) NOT NULL,
  moneda               CHAR(3) NOT NULL DEFAULT 'COP',
  estado               ENUM('pendiente','aprobado','rechazado','error','anulado','reembolsado') NOT NULL DEFAULT 'pendiente',
  fecha_creacion       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_pago           DATETIME NULL,
  fecha_reembolso      DATETIME NULL,
  id_usuario_reembolso INT UNSIGNED NULL,
  CONSTRAINT fk_pago_pedido  FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido),
  CONSTRAINT fk_pago_usuario FOREIGN KEY (id_usuario_reembolso) REFERENCES usuario(id_usuario),
  INDEX idx_pago_pedido (id_pedido)
) ENGINE=InnoDB;

CREATE TABLE evento_pago (
  id_evento       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pago         INT UNSIGNED NULL,
  id_evento_wompi VARCHAR(80) NOT NULL UNIQUE,    -- checksum del evento: evita procesar dos veces el mismo evento
  tipo            VARCHAR(60) NOT NULL,
  payload         LONGTEXT NOT NULL,
  firma_valida    TINYINT(1) NOT NULL,
  procesado       TINYINT(1) NOT NULL DEFAULT 0,
  fecha_recepcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evento_pago FOREIGN KEY (id_pago) REFERENCES pago(id_pago)
) ENGINE=InnoDB;

-- ---------- PQR ----------
CREATE TABLE pqr (
  id_pqr               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario           INT UNSIGNED NOT NULL,
  tipo                 ENUM('peticion','queja','reclamo','sugerencia') NOT NULL,
  asunto               VARCHAR(150) NOT NULL,
  mensaje              TEXT NOT NULL,
  estado               ENUM('pendiente','respondida','cerrada') NOT NULL DEFAULT 'pendiente',
  respuesta            TEXT NULL,
  id_usuario_respuesta INT UNSIGNED NULL,
  fecha_creacion       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_respuesta      DATETIME NULL,
  CONSTRAINT fk_pqr_usuario   FOREIGN KEY (id_usuario)           REFERENCES usuario(id_usuario),
  CONSTRAINT fk_pqr_respuesta FOREIGN KEY (id_usuario_respuesta) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- ---------- CONFIGURACIÓN (parámetros e información de la tienda) ----------
CREATE TABLE configuracion (
  clave               VARCHAR(60) PRIMARY KEY,
  valor               TEXT NOT NULL,
  descripcion         VARCHAR(255) NULL,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
