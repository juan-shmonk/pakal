-- ============================================================
--  PAKAL — Esquema de Base de Datos (MVP)
--  Motor: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pedido_items;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS carrito_items;
DROP TABLE IF EXISTS carritos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;

-- ── Tabla: usuarios ──────────────────────────────────────────
CREATE TABLE usuarios (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(100)    NOT NULL,
  apellido      VARCHAR(100)    NOT NULL,
  email         VARCHAR(255)    NOT NULL,
  telefono      VARCHAR(25)     NULL DEFAULT NULL,
  password_hash VARCHAR(255)    NOT NULL,
  rol           ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  activo        TINYINT(1)      NOT NULL DEFAULT 1,
  newsletter    TINYINT(1)      NOT NULL DEFAULT 0,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email),
  KEY idx_rol    (rol),
  KEY idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: categorias ────────────────────────────────────────
CREATE TABLE categorias (
  id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(100) NOT NULL,
  slug    VARCHAR(120) NOT NULL,
  genero  ENUM('hombre','mujer','infantil','unisex') NOT NULL DEFAULT 'unisex',
  activo  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: productos ─────────────────────────────────────────
CREATE TABLE productos (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(255)    NOT NULL,
  slug          VARCHAR(280)    NOT NULL,
  marca         VARCHAR(100)    NOT NULL DEFAULT 'PAKAL',
  coleccion     VARCHAR(150)    NULL DEFAULT NULL,
  descripcion   TEXT            NULL,
  material      VARCHAR(255)    NULL DEFAULT NULL,
  precio        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  precio_rebaja DECIMAL(10,2)   NULL DEFAULT NULL,
  categoria_id  INT UNSIGNED    NOT NULL,
  stock         INT             NOT NULL DEFAULT 0,
  badge         ENUM('nuevo','rebaja','exclusivo','') NOT NULL DEFAULT '',
  destacado     TINYINT(1)      NOT NULL DEFAULT 0,
  estado        ENUM('activo','borrador','agotado') NOT NULL DEFAULT 'activo',
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug),
  KEY idx_categoria (categoria_id),
  KEY idx_estado    (estado),
  KEY idx_destacado (destacado),
  CONSTRAINT fk_prod_cat FOREIGN KEY (categoria_id)
    REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: carritos ──────────────────────────────────────────
-- Un carrito por usuario. Se crea al primer agregado.
CREATE TABLE carritos (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  creado_en  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario (usuario_id),
  CONSTRAINT fk_car_usr FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: carrito_items ─────────────────────────────────────
CREATE TABLE carrito_items (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  carrito_id  INT UNSIGNED  NOT NULL,
  producto_id INT UNSIGNED  NOT NULL,
  cantidad    INT           NOT NULL DEFAULT 1,
  precio_unit DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_ci_car  FOREIGN KEY (carrito_id)  REFERENCES carritos(id)  ON DELETE CASCADE,
  CONSTRAINT fk_ci_prod FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: pedidos ───────────────────────────────────────────
CREATE TABLE pedidos (
  id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED  NOT NULL,
  nombre     VARCHAR(200)  NOT NULL,
  email      VARCHAR(255)  NOT NULL,
  telefono   VARCHAR(25)   NULL DEFAULT NULL,
  direccion  TEXT          NOT NULL,
  total      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado     ENUM('pendiente','confirmado','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_usuario (usuario_id),
  KEY idx_estado  (estado),
  CONSTRAINT fk_ped_usr FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: pedido_items ──────────────────────────────────────
-- Guarda una copia del nombre y precio al momento de la compra
-- (el producto puede cambiar o eliminarse después)
CREATE TABLE pedido_items (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  pedido_id   INT UNSIGNED  NOT NULL,
  producto_id INT UNSIGNED  NOT NULL,
  nombre      VARCHAR(255)  NOT NULL,
  cantidad    INT           NOT NULL DEFAULT 1,
  precio_unit DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_pi_ped  FOREIGN KEY (pedido_id)  REFERENCES pedidos(id)  ON DELETE CASCADE,
  CONSTRAINT fk_pi_prod FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Datos iniciales: categorías ──────────────────────────────
INSERT INTO categorias (nombre, slug, genero) VALUES
('Blazers y Sacos',    'blazers-sacos', 'hombre'),
('Camisas',            'camisas',       'hombre'),
('Pantalones',         'pantalones',    'hombre'),
('Abrigos',            'abrigos',       'hombre'),
('Suéteres y Tejidos', 'sueteres',      'hombre'),
('Camisetas',          'camisetas',     'hombre'),
('Zapatos',            'zapatos',       'hombre'),
('Accesorios',         'accesorios',    'unisex'),
('Vestidos',           'vestidos',      'mujer'),
('Blusas',             'blusas',        'mujer'),
('Faldas',             'faldas',        'mujer'),
('Niño',               'nino',          'infantil'),
('Niña',               'nina',          'infantil');

-- ── Datos de prueba: productos ───────────────────────────────
INSERT INTO productos (nombre, slug, marca, coleccion, descripcion, material, precio, precio_rebaja, categoria_id, stock, badge, destacado, estado) VALUES
('Blazer Estructurado Chichen', 'blazer-estructurado-chichen',
 'PAKAL Heritage', 'Colección Tikal',
 'Blazer de silueta definida inspirado en la arquitectura del templo de Chichen Itzá. Interior forrado en seda natural.',
 '70% Lana virgen · 20% Seda natural · 10% Cachemira',
 6800.00, NULL, 1, 24, 'nuevo', 1, 'activo'),

('Pantalón Sastre Uxmal', 'pantalon-sastre-uxmal',
 'PAKAL Noir', 'Colección Tikal',
 'Pantalón de corte recto inspirado en las líneas geométricas de Uxmal. Confeccionado en lana premium.',
 '100% Lana virgen',
 3200.00, NULL, 3, 38, '', 1, 'activo'),

('Camisa de Lino Palenque', 'camisa-lino-palenque',
 'PAKAL Origin', 'Colección Chichen',
 'Camisa de lino natural con bordados artesanales mayas. Ligera y elegante para cualquier ocasión.',
 '100% Lino natural',
 2700.00, 1890.00, 2, 52, 'rebaja', 1, 'activo'),

('Abrigo de Mezcla Cobá', 'abrigo-mezcla-coba',
 'PAKAL Heritage', 'Colección Tikal',
 'Abrigo largo de mezcla premium con detalles artesanales. Inspirado en la selva de Cobá.',
 '60% Lana · 30% Cachemira · 10% Seda',
 12400.00, NULL, 4, 2, 'nuevo', 1, 'activo'),

('Polo Piqué Tulum', 'polo-pique-tulum',
 'PAKAL Noir', 'PAKAL Atemporal',
 'Polo de algodón piqué de alta calidad con escote clásico y botones de nácar. Prenda versátil.',
 '100% Algodón pima',
 1650.00, NULL, 6, 60, '', 0, 'activo'),

('Suéter Merino Xibalbá', 'sueter-merino-xibalba',
 'PAKAL Origin', 'Colección Chichen',
 'Suéter de lana merino extrafino con cuello redondo. Suave, cálido y elegante.',
 '100% Lana merino extrafina',
 4100.00, NULL, 5, 0, '', 0, 'agotado'),

('Vestido Bordado Ixchel', 'vestido-bordado-ixchel',
 'PAKAL Femme', 'Colección Tikal',
 'Vestido midi con bordados a mano inspirados en la diosa maya Ixchel. Tela fluida de seda natural.',
 '80% Seda · 20% Algodón',
 5200.00, NULL, 9, 18, 'exclusivo', 1, 'activo'),

('Blusa Seda Sacbé', 'blusa-seda-sacbe',
 'PAKAL Femme', 'Colección Chichen',
 'Blusa de seda natural con manga fruncida y cuello discreto. Elegante y cómoda para el día a día.',
 '100% Seda natural',
 2800.00, 2100.00, 10, 30, 'rebaja', 0, 'activo');
