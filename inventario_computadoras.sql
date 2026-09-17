-- Base de datos para MyFirstAPP
-- Compatible con login.php, productos.php, movimientos.php y dashboard.php
-- Usuario de prueba: admin@example.com
-- Contraseña: Admin123!

CREATE DATABASE IF NOT EXISTS inventario_computadoras
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventario_computadoras;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS movimientos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'empleado') NOT NULL DEFAULT 'empleado',
  status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE productos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  code VARCHAR(50) NOT NULL,
  price DECIMAL(10,2) UNSIGNED NOT NULL,
  available INT UNSIGNED NOT NULL DEFAULT 0,
  minimum_stock INT UNSIGNED NOT NULL DEFAULT 10,
  category VARCHAR(100) DEFAULT NULL,
  brand VARCHAR(100) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_productos_code (code),
  KEY idx_productos_name (name),
  KEY idx_productos_active_stock (active, available),
  KEY idx_productos_category (category)
) ENGINE=InnoDB;

CREATE TABLE movimientos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  type ENUM('Entrada', 'Salida') NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  notes VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_movimientos_product (product_id),
  KEY idx_movimientos_created (created_at),
  KEY idx_movimientos_type (type),
  CONSTRAINT fk_movimientos_productos
    FOREIGN KEY (product_id) REFERENCES productos (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO users (id, name, email, password, role, status) VALUES
  (1, 'Administrador', 'admin@example.com', '$2y$12$oH7Be35Y4QWsfwEnHBCWJ.ZfaNd7fVhbIMFaRNNCp2fnPBYhybOK6', 'admin', 'active'),
  (2, 'María López', 'maria@example.com', '$2y$12$oH7Be35Y4QWsfwEnHBCWJ.ZfaNd7fVhbIMFaRNNCp2fnPBYhybOK6', 'empleado', 'active'),
  (3, 'Usuario Inactivo', 'inactivo@example.com', '$2y$12$oH7Be35Y4QWsfwEnHBCWJ.ZfaNd7fVhbIMFaRNNCp2fnPBYhybOK6', 'empleado', 'inactive');

INSERT INTO productos
  (id, name, code, price, available, minimum_stock, category, brand, description, active)
VALUES
  (1, 'Teclado mecánico RGB', 'TEC-RGB-001', 899.00, 25, 10, 'Periféricos', 'Redragon', 'Teclado mecánico con iluminación RGB.', 1),
  (2, 'Mouse inalámbrico', 'MOU-WL-002', 349.50, 8, 10, 'Periféricos', 'Logitech', 'Mouse inalámbrico con receptor USB.', 1),
  (3, 'Monitor LED 24 pulgadas', 'MON-24-003', 3299.00, 6, 5, 'Monitores', 'Samsung', 'Monitor Full HD de 24 pulgadas.', 1),
  (4, 'Cable HDMI 2.1', 'CAB-HDMI-004', 249.00, 40, 15, 'Cables', 'UGREEN', 'Cable HDMI 2.1 de dos metros.', 1),
  (5, 'Memoria USB 64 GB', 'USB-64-005', 189.00, 0, 10, 'Almacenamiento', 'Kingston', 'Unidad USB 3.2 de 64 GB.', 1),
  (6, 'Disco SSD 1 TB', 'SSD-1TB-006', 1399.00, 12, 5, 'Almacenamiento', 'Crucial', 'Unidad SSD SATA de 1 TB.', 1),
  (7, 'Audífonos con micrófono', 'AUD-MIC-007', 649.00, 7, 10, 'Audio', 'HyperX', 'Audífonos para computadora con micrófono.', 1),
  (8, 'Webcam Full HD', 'WEB-FHD-008', 799.00, 16, 5, 'Video', 'Logitech', 'Cámara web con resolución Full HD.', 1),
  (9, 'Adaptador USB-C', 'ADA-USBC-009', 299.00, 30, 10, 'Adaptadores', 'Anker', 'Adaptador USB-C a USB-A.', 1),
  (10, 'Bocinas 2.0', 'BOC-20-010', 499.00, 0, 5, 'Audio', 'Logitech', 'Par de bocinas compactas para escritorio.', 1),
  (11, 'Producto descontinuado', 'DESC-011', 100.00, 0, 5, 'Otros', NULL, 'Registro inactivo para probar el borrado lógico.', 0);

INSERT INTO movimientos (id, product_id, type, quantity, notes, created_at) VALUES
  (1, 1, 'Entrada', 30, 'Inventario inicial', DATE_SUB(NOW(), INTERVAL 15 DAY)),
  (2, 2, 'Entrada', 15, 'Inventario inicial', DATE_SUB(NOW(), INTERVAL 14 DAY)),
  (3, 3, 'Entrada', 10, 'Compra a proveedor', DATE_SUB(NOW(), INTERVAL 12 DAY)),
  (4, 4, 'Entrada', 50, 'Inventario inicial', DATE_SUB(NOW(), INTERVAL 10 DAY)),
  (5, 5, 'Entrada', 10, 'Inventario inicial', DATE_SUB(NOW(), INTERVAL 9 DAY)),
  (6, 5, 'Salida', 10, 'Venta de existencias', DATE_SUB(NOW(), INTERVAL 7 DAY)),
  (7, 7, 'Entrada', 12, 'Compra a proveedor', DATE_SUB(NOW(), INTERVAL 6 DAY)),
  (8, 2, 'Salida', 7, 'Venta mostrador', DATE_SUB(NOW(), INTERVAL 3 DAY)),
  (9, 4, 'Salida', 10, 'Venta a cliente', DATE_SUB(NOW(), INTERVAL 1 DAY)),
  (10, 1, 'Salida', 5, 'Venta mostrador', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
  (11, 3, 'Salida', 4, 'Venta mostrador', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
  (12, 9, 'Entrada', 10, 'Reposición de inventario', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

ALTER TABLE users AUTO_INCREMENT = 4;
ALTER TABLE productos AUTO_INCREMENT = 12;
ALTER TABLE movimientos AUTO_INCREMENT = 13;

-- Verificación rápida después de importar:
-- SELECT * FROM users;
-- SELECT * FROM productos WHERE active = 1;
-- SELECT * FROM movimientos ORDER BY created_at DESC;
