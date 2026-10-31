-- =========================================================
--  La Nueva Esperanza - Esquema de base de datos
--  MySQL 5.7+ / MariaDB 10.3+   (utf8mb4)
-- =========================================================
CREATE DATABASE IF NOT EXISTS lanuevaesperanza
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lanuevaesperanza;

-- ---------- Usuarios y seguridad ----------
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol           ENUM('superadmin','editor','lector','atencion') NOT NULL DEFAULT 'lector',
  activo        TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_login  DATETIME NULL,
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_cursor (creado_en, id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_intentos (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email     VARCHAR(160) NOT NULL,
  ip        VARCHAR(45)  NOT NULL,
  exitoso   TINYINT(1)   NOT NULL DEFAULT 0,
  creado_en DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_li_ip (ip, creado_en),
  KEY idx_li_email (email, creado_en)
) ENGINE=InnoDB;

-- ---------- Catálogo ----------
CREATE TABLE IF NOT EXISTS rubros (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NULL,
  color       VARCHAR(7)   NOT NULL DEFAULT '#0ea5e9',
  orden       INT          NOT NULL DEFAULT 0,
  activo      TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_rubros_nombre (nombre),
  KEY idx_rubros_cursor (creado_en, id)
) ENGINE=InnoDB;

-- Bolsas y combos
CREATE TABLE IF NOT EXISTS productos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rubro_id    INT UNSIGNED NULL,
  tipo        ENUM('bolsa','combo') NOT NULL DEFAULT 'bolsa',
  nombre      VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  contenido   TEXT NULL COMMENT 'Un artículo por línea',
  precio      DECIMAL(12,2) NOT NULL DEFAULT 0,
  moneda      VARCHAR(5)   NOT NULL DEFAULT 'USD',
  imagen      VARCHAR(255) NULL,
  activo      TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_productos_cursor (creado_en, id),
  KEY idx_productos_rubro (rubro_id),
  CONSTRAINT fk_productos_rubro FOREIGN KEY (rubro_id) REFERENCES rubros(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_precios (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  producto_id     INT UNSIGNED NOT NULL,
  precio_anterior DECIMAL(12,2) NOT NULL,
  precio_nuevo    DECIMAL(12,2) NOT NULL,
  moneda          VARCHAR(5) NOT NULL,
  usuario_id      INT UNSIGNED NULL,
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_hp_cursor (creado_en, id),
  KEY idx_hp_producto (producto_id),
  CONSTRAINT fk_hp_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_hp_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- Entregas semanales ----------
CREATE TABLE IF NOT EXISTS entregas (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fecha_entrega   DATE NOT NULL,
  hora            TIME NULL,
  sector          VARCHAR(150) NOT NULL,
  direccion       VARCHAR(255) NOT NULL,
  cantidad_bolsas INT UNSIGNED NOT NULL DEFAULT 0,
  producto_id     INT UNSIGNED NULL,
  responsable     VARCHAR(150) NOT NULL,
  telefono        VARCHAR(40)  NULL,
  estado          ENUM('programada','en_camino','entregada','cancelada') NOT NULL DEFAULT 'programada',
  notas           TEXT NULL,
  creado_por      INT UNSIGNED NULL,
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_entregas_cursor (fecha_entrega, id),
  KEY idx_entregas_estado (estado),
  CONSTRAINT fk_entregas_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL,
  CONSTRAINT fk_entregas_usuario  FOREIGN KEY (creado_por)  REFERENCES usuarios(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- Contenido ----------
CREATE TABLE IF NOT EXISTS estatutos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo      VARCHAR(200) NOT NULL,
  contenido   MEDIUMTEXT NULL,
  archivo_pdf VARCHAR(255) NULL,
  orden       INT NOT NULL DEFAULT 0,
  publicado   TINYINT(1) NOT NULL DEFAULT 1,
  creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_estatutos_cursor (creado_en, id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS publicaciones (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo            VARCHAR(200) NOT NULL,
  resumen           VARCHAR(300) NULL,
  contenido         MEDIUMTEXT NULL,
  imagen            VARCHAR(255) NULL,
  publicado         TINYINT(1) NOT NULL DEFAULT 1,
  fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_por        INT UNSIGNED NULL,
  creado_en         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_publicaciones_cursor (fecha_publicacion, id),
  CONSTRAINT fk_pub_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(80) PRIMARY KEY,
  valor TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auditoria (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  accion     VARCHAR(40)  NOT NULL,
  entidad    VARCHAR(60)  NOT NULL,
  entidad_id INT UNSIGNED NULL,
  detalle    VARCHAR(500) NULL,
  ip         VARCHAR(45)  NULL,
  creado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_auditoria_cursor (creado_en, id),
  CONSTRAINT fk_aud_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('nombre_organizacion', 'La Nueva Esperanza'),
  ('lema', 'Llevando esperanza a cada hogar'),
  ('telefono', ''),
  ('email_contacto', ''),
  ('direccion', ''),
  ('moneda_defecto', 'USD'),
  ('logo', '');

-- ---------- Comunicación con Productores (Literal G del Objeto) ----------
CREATE TABLE IF NOT EXISTS comunicaciones_productores (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(150) NOT NULL,
  sector_comunidad  VARCHAR(150) NOT NULL,
  telefono          VARCHAR(40)  NOT NULL,
  correo            VARCHAR(160) NULL,
  tipo              ENUM('oferta_rubro','requerimiento_tecnico','sugerencia_observacion','otro') NOT NULL DEFAULT 'oferta_rubro',
  asunto            VARCHAR(200) NOT NULL,
  mensaje           TEXT NOT NULL,
  adjunto           VARCHAR(255) NULL,
  estado            ENUM('pendiente','en_revision','atendido','archivado') NOT NULL DEFAULT 'pendiente',
  respuesta_interna TEXT NULL,
  atendido_por      INT UNSIGNED NULL,
  atendido_en       DATETIME NULL,
  creado_en         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cp_cursor (creado_en, id),
  KEY idx_cp_estado (estado),
  KEY idx_cp_tipo (tipo),
  CONSTRAINT fk_cp_usuario FOREIGN KEY (atendido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;
