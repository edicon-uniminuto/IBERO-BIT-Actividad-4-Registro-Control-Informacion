<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/conexion.php
   TECNOLOGÍAS: PHP 8, PDO, MySQL
   DESCRIPCIÓN: Centraliza la conexión PDO a la base de datos local de Laragon.
   ============================================================
*/
declare(strict_types=1);

function conectarBaseDatos(): PDO
{
    $host = getenv('APP_DB_HOST') ?: '127.0.0.1';
    $port = getenv('APP_DB_PORT') ?: '3306';
    $name = getenv('APP_DB_NAME') ?: 'ibero_tienda';
    $user = getenv('APP_DB_USER') ?: 'root';
    $password = getenv('APP_DB_PASSWORD') !== false ? getenv('APP_DB_PASSWORD') : '';

    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // Las marcas temporales se conservan en UTC y se presentan en horario de Bogotá.
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}
