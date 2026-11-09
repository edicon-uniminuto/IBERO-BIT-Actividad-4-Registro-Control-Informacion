<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/inicio.php
   TECNOLOGÍAS: PHP 8, PDO, sesiones
   DESCRIPCIÓN: Inicializa la sesión, carga las dependencias y prepara la conexión PDO.
   ============================================================
*/
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'use_strict_mode' => true, 'cookie_samesite' => 'Lax']);
}
require_once dirname(__DIR__) . '/conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/inventario.php';
require_once __DIR__ . '/categorias.php';

try {
    $pdo = conectarBaseDatos();
} catch (PDOException $e) {
    error_log('[IberoTech] No se pudo conectar a la base de datos: ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    exit('<!doctype html><html lang="es"><meta charset="utf-8"><title>IberoTech | Conexión</title><body style="font:16px system-ui;margin:4rem auto;max-width:640px"><h1>Base de datos no disponible</h1><p>Inicia MySQL en Laragon, importa <code>database/01_schema.sql</code> y <code>database/02_seed.sql</code>, y revisa la configuración en <code>conexion.php</code>.</p><a href="index.php">Volver a intentar</a></body></html>');
}
