<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    $sql = "SELECT ServicioID, Codigo, Nombre, Descripcion
            FROM organizacion.Servicio
            WHERE Estado = 1
            ORDER BY ServicioID ASC";

    $stmt = $pdo->query($sql);
    $servicios = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $servicios], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
