-- ============================================================
--  PAKAL — Esquema de Base de Datos (solo tablas)
--  La BD se crea y selecciona desde setup.php con PDO.
--  Motor: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Tabla: usuarios ─────────────────────────────────────────
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(100)    NOT NULL,
  apellido      VARCHAR(100)    NOT NULL,
  email         VARCHAR(255)    NOT NULL,
  telefono      VARCHAR(25)     NULL     DEFAULT NULL,
  password_hash VARCHAR(255)    NOT NULL,
  rol           ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  activo        TINYINT(1)      NOT NULL DEFAULT 1,
  newsletter    TINYINT(1)      NOT NULL DEFAULT 0,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE  KEY uq_email (email),
  KEY     idx_rol    (rol),
  KEY     idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: categorias ───────────────────────────────────────
DROP TABLE IF EXISTS categorias;
CREATE TABLE categorias (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre    VARCHAR(100) NOT NULL,
  slug      VARCHAR(120) NOT NULL,
  genero    ENUM('hombre','mujer','infantil','unisex') NOT NULL DEFAULT 'unisex',
  activo    TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: productos ────────────────────────────────────────
DROP TABLE IF EXISTS productos;
CREATE TABLE productos (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(255)    NOT NULL,
  slug          VARCHAR(280)    NOT NULL,
  marca         VARCHAR(100)    NOT NULL,
  coleccion     VARCHAR(150)    NULL DEFAULT NULL,
  descripcion   TEXT            NULL,
  material      VARCHAR(255)    NULL DEFAULT NULL,
  origen        VARCHAR(100)    NULL DEFAULT NULL,
  precio        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  precio_rebaja DECIMAL(10,2)   NULL     DEFAULT NULL,
  categoria_id  INT UNSIGNED    NOT NULL,
  stock         INT             NOT NULL DEFAULT 0,
  badge         ENUM('nuevo','rebaja','exclusivo','') NOT NULL DEFAULT '',
  destacado     TINYINT(1)      NOT NULL DEFAULT 0,
  estado        ENUM('activo','borrador','agotado')   NOT NULL DEFAULT 'activo',
  imagen_principal VARCHAR(500) NULL DEFAULT NULL,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE  KEY uq_slug (slug),
  KEY     idx_categoria (categoria_id),
  KEY     idx_estado    (estado),
  KEY     idx_destacado (destacado),
  CONSTRAINT fk_prod_cat FOREIGN KEY (categoria_id)
    REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: producto_imagenes ────────────────────────────────
DROP TABLE IF EXISTS producto_imagenes;
CREATE TABLE producto_imagenes (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  producto_id INT UNSIGNED NOT NULL,
  ruta        VARCHAR(500) NOT NULL,
  orden       TINYINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_producto (producto_id),
  CONSTRAINT fk_img_prod FOREIGN KEY (producto_id)
    REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: wishlist ─────────────────────────────────────────
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
  usuario_id  INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, producto_id),
  CONSTRAINT fk_wish_usr  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
  CONSTRAINT fk_wish_prod FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: carritos ─────────────────────────────────────────
DROP TABLE IF EXISTS carritos;
CREATE TABLE carritos (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id  INT UNSIGNED NULL DEFAULT NULL,
  sesion_id   VARCHAR(128) NULL DEFAULT NULL,
  creado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_usr (usuario_id),
  KEY idx_ses (sesion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: carrito_items ────────────────────────────────────
DROP TABLE IF EXISTS carrito_items;
CREATE TABLE carrito_items (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  carrito_id  INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  talla       VARCHAR(10)  NOT NULL DEFAULT '',
  color       VARCHAR(50)  NOT NULL DEFAULT '',
  cantidad    INT          NOT NULL DEFAULT 1,
  precio_unit DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_ci_car  FOREIGN KEY (carrito_id)  REFERENCES carritos(id) ON DELETE CASCADE,
  CONSTRAINT fk_ci_prod FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Datos iniciales: categorías ─────────────────────────────
INSERT IGNORE INTO categorias (nombre, slug, genero) VALUES
('Blazers y Sacos', 'blazers-sacos', 'hombre'),
('Camisas',         'camisas',       'hombre'),
('Pantalones',      'pantalones',    'hombre'),
('Abrigos',         'abrigos',       'hombre'),
('Suéteres y Tejidos', 'sueteres',   'hombre'),
('Camisetas',       'camisetas',     'hombre'),
('Zapatos',         'zapatos',       'hombre'),
('Accesorios',      'accesorios',    'unisex'),
('Vestidos',        'vestidos',      'mujer'),
('Blusas',          'blusas',        'mujer'),
('Faldas',          'faldas',        'mujer'),
('Niño',            'nino',          'infantil'),
('Niña',            'nina',          'infantil');

SET FOREIGN_KEY_CHECKS = 1;
