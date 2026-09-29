<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    $sql = "SELECT p.PersonalID, p.CodigoEmpleado, per.PersonaID, per.NumeroDocumento,
                   per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                   c.Nombre AS Cargo, esp.Nombre AS Especialidad, prof.Nombre AS Profesion
            FROM rrhh.Personal p
            INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
            LEFT JOIN rrhh.AsignacionPersonal ap ON p.PersonalID = ap.PersonalID AND ap.EsActual = 1
            LEFT JOIN rrhh.Cargo c ON ap.CargoID = c.CargoID
            LEFT JOIN rrhh.Especialidad esp ON ap.EspecialidadID = esp.EspecialidadID
            LEFT JOIN rrhh.Profesion prof ON ap.ProfesionID = prof.ProfesionID
            WHERE p.Estado = 1
            ORDER BY per.ApellidoPaterno, per.Nombres";

    $stmt = $pdo->query($sql);
    $medicos = $stmt->fetchAll();

    foreach ($medicos as &$m) {
        $m['NombreCompleto'] = trim($m['ApellidoPaterno'] . ' ' . ($m['ApellidoMaterno'] ?? '') . ', ' . $m['Nombres']);
    }

    echo json_encode(['success' => true, 'data' => $medicos], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
