-- ============================================================
-- PROYECTO: IberoTech - Actividad 4 - Registro y control de información
-- INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
-- ESTUDIANTE: Edilberto Elias Contreras Sierra
-- DOCENTE: Jairo Niño
-- ARCHIVO: database/01_schema.sql
-- TECNOLOGÍAS: MySQL 8 / MariaDB, SQL DDL
-- DESCRIPCIÓN: Crea la base de datos y las tablas con claves e integridad relacional.
-- ============================================================

CREATE DATABASE IF NOT EXISTS ibero_tienda
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ibero_tienda;

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    marca VARCHAR(60) NOT NULL DEFAULT 'Genérica',
    categoria VARCHAR(60) NOT NULL,
    precio DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria)
        REFERENCES categorias(nombre) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_producto_nombre(nombre),
    INDEX idx_producto_marca(marca),
    INDEX idx_producto_categoria(categoria)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS movimientos_stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NULL,
    producto_nombre VARCHAR(120) NOT NULL,
    variacion INT NOT NULL,
    motivo VARCHAR(100) NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimiento_producto FOREIGN KEY(producto_id)
        REFERENCES productos(id) ON DELETE SET NULL,
    INDEX idx_movimientos_fecha(creado_en)
) ENGINE=InnoDB;
