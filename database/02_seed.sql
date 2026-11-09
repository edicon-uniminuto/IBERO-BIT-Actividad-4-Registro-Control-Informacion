-- ============================================================
-- PROYECTO: IberoTech - Actividad 4 - Registro y control de información
-- INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
-- ESTUDIANTE: Edilberto Elias Contreras Sierra
-- DOCENTE: Jairo Niño
-- ARCHIVO: database/02_seed.sql
-- TECNOLOGÍAS: MySQL 8 / MariaDB, SQL DML
-- DESCRIPCIÓN: Carga las categorías y los cinco productos de ejemplo de la clase.
-- ============================================================

USE ibero_tienda;

INSERT IGNORE INTO categorias (nombre) VALUES
 ('Computadores'), ('Monitores'), ('Accesorios'), ('Almacenamiento'), ('Audio'), ('Redes');

INSERT INTO productos (nombre, marca, categoria, precio, stock)
SELECT 'Portátil Ryzen 5 16GB', 'Lenovo', 'Computadores', 2450000.00, 6
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Portátil Ryzen 5 16GB');
INSERT INTO productos (nombre, marca, categoria, precio, stock)
SELECT 'Mouse Vertical Ergonómico', 'Logitech', 'Accesorios', 68000.00, 15
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Mouse Vertical Ergonómico');
INSERT INTO productos (nombre, marca, categoria, precio, stock)
SELECT 'Monitor IPS 27 Pulgadas', 'LG', 'Monitores', 790000.00, 4
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Monitor IPS 27 Pulgadas');
INSERT INTO productos (nombre, marca, categoria, precio, stock)
SELECT 'Teclado Mecánico RGB', 'Redragon', 'Accesorios', 185000.00, 0
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Teclado Mecánico RGB');
INSERT INTO productos (nombre, marca, categoria, precio, stock)
SELECT 'Disco Sólido SSD 1TB NVMe', 'Kingston', 'Almacenamiento', 310000.00, 9
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Disco Sólido SSD 1TB NVMe');
