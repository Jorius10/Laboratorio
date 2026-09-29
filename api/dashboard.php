<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    // 1. Contadores del Dashboard
    $sqlCounters = "SELECT
        (SELECT COUNT(*) FROM laboratorio.Solicitud WHERE CAST(FechaSolicitud AS DATE) = CAST(GETDATE() AS DATE)) AS AnalisisHoy,
        (SELECT COUNT(*) FROM laboratorio.Solicitud WHERE Estado = 'PENDIENTE') AS Pendientes,
        (SELECT COUNT(*) FROM laboratorio.Solicitud WHERE Estado = 'VALIDADO') AS Validados,
        (SELECT COUNT(*) FROM laboratorio.Solicitud WHERE Estado = 'EMITIDO') AS Emitidos,
        (SELECT COUNT(*) FROM laboratorio.Solicitud WHERE Estado = 'ANULADO') AS Anulados,
        (SELECT COUNT(*) FROM clinico.Paciente WHERE Estado = 1) AS TotalPacientes,
        (SELECT COUNT(*) FROM laboratorio.Muestra WHERE Estado = 'PENDIENTE') AS MuestrasPendientes";
    $counters = $pdo->query($sqlCounters)->fetch();

    // 2. Ultimos analisis recientes
    $sqlRecientes = "SELECT TOP 8 s.SolicitudID, s.CodigoSolicitud, s.FechaSolicitud, s.Prioridad, s.Estado,
                            s.DiagnosticoPresuntivo,
                            p.HistoriaClinica, per.NumeroDocumento,
                            per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                            serv.Nombre AS ServicioNombre,
                            (SELECT COUNT(*) FROM laboratorio.DetalleSolicitud ds WHERE ds.SolicitudID = s.SolicitudID) AS TotalExamenes
                     FROM laboratorio.Solicitud s
                     INNER JOIN clinico.Paciente p ON s.PacienteID = p.PacienteID
                     INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                     LEFT JOIN organizacion.Servicio serv ON s.ServicioSolicitanteID = serv.ServicioID
                     ORDER BY s.FechaSolicitud DESC, s.SolicitudID DESC";
    $recientes = $pdo->query($sqlRecientes)->fetchAll();

    foreach ($recientes as &$r) {
        $r['NombreCompleto'] = trim($r['ApellidoPaterno'] . ' ' . ($r['ApellidoMaterno'] ?? '') . ', ' . $r['Nombres']);
    }

    // 3. Demanda por Servicio
    $sqlServicios = "SELECT TOP 6 serv.Nombre AS ServicioNombre, COUNT(*) AS Total
                     FROM laboratorio.Solicitud s
                     LEFT JOIN organizacion.Servicio serv ON s.ServicioSolicitanteID = serv.ServicioID
                     GROUP BY serv.Nombre
                     ORDER BY Total DESC";
    $demandaServicios = $pdo->query($sqlServicios)->fetchAll();

    // 4. Distribucion por Estado
    $sqlEstados = "SELECT Estado, COUNT(*) AS Total
                   FROM laboratorio.Solicitud
                   GROUP BY Estado";
    $distEstados = $pdo->query($sqlEstados)->fetchAll();

    // 5. Tendencia de los ultimos 7 dias
    $sqlTendencia = "SELECT CAST(FechaSolicitud AS DATE) AS Fecha, COUNT(*) AS Total
                     FROM laboratorio.Solicitud
                     WHERE FechaSolicitud >= DATEADD(day, -7, GETDATE())
                     GROUP BY CAST(FechaSolicitud AS DATE)
                     ORDER BY Fecha ASC";
    $tendencia = $pdo->query($sqlTendencia)->fetchAll();

    // 6. Top Examenes Solicitados
    $sqlTopExamenes = "SELECT TOP 5 ex.Nombre AS ExamenNombre, COUNT(*) AS Total
                       FROM laboratorio.DetalleSolicitud ds
                       INNER JOIN laboratorio.Examen ex ON ds.ExamenID = ex.ExamenID
                       GROUP BY ex.Nombre
                       ORDER BY Total DESC";
    $topExamenes = $pdo->query($sqlTopExamenes)->fetchAll();

    echo json_encode([
        'success' => true,
        'counters' => $counters,
        'recientes' => $recientes,
        'servicios_demanda' => $demandaServicios,
        'distribucion_estados' => $distEstados,
        'tendencia_dias' => $tendencia,
        'top_examenes' => $topExamenes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
