<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    $fechaInicio = trim($_GET['fecha_inicio'] ?? date('Y-m-01'));
    $fechaFin    = trim($_GET['fecha_fin'] ?? date('Y-m-d'));
    $servicioId  = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : 0;
    $estado      = trim($_GET['estado'] ?? '');

    // Formatear fechas para cubrir el dia completo
    $fInicioFull = $fechaInicio . ' 00:00:00';
    $fFinFull    = $fechaFin . ' 23:59:59';

    // 1. Consulta del listado detallado
    $whereParts = ["s.FechaSolicitud BETWEEN ? AND ?"];
    $bindings = [$fInicioFull, $fFinFull];

    if ($servicioId > 0) {
        $whereParts[] = "s.ServicioSolicitanteID = ?";
        $bindings[] = $servicioId;
    }

    if ($estado !== '' && $estado !== 'TODOS') {
        $whereParts[] = "s.Estado = ?";
        $bindings[] = $estado;
    }

    $whereSql = implode(' AND ', $whereParts);

    $sqlList = "SELECT s.SolicitudID, s.CodigoSolicitud, s.FechaSolicitud, s.Prioridad, s.Estado,
                       s.DiagnosticoPresuntivo, s.Observacion,
                       p.PacienteID, p.HistoriaClinica, per.NumeroDocumento,
                       per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                       per.FechaNacimiento, sx.Codigo AS SexoCodigo,
                       serv.Nombre AS ServicioNombre,
                       docPer.Nombres AS MedicoNombres, docPer.ApellidoPaterno AS MedicoPaterno,
                       (SELECT COUNT(*) FROM laboratorio.DetalleSolicitud ds WHERE ds.SolicitudID = s.SolicitudID) AS TotalExamenes
                FROM laboratorio.Solicitud s
                INNER JOIN clinico.Paciente p ON s.PacienteID = p.PacienteID
                INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                LEFT JOIN catalogo.Sexo sx ON per.SexoID = sx.SexoID
                LEFT JOIN organizacion.Servicio serv ON s.ServicioSolicitanteID = serv.ServicioID
                LEFT JOIN rrhh.Personal doc ON s.PersonalSolicitanteID = doc.PersonalID
                LEFT JOIN core.Persona docPer ON doc.PersonaID = docPer.PersonaID
                WHERE {$whereSql}
                ORDER BY s.FechaSolicitud DESC";

    $stmt = $pdo->prepare($sqlList);
    $stmt->execute($bindings);
    $rows = $stmt->fetchAll();

    $now = new DateTime();
    $totalAnalisis = count($rows);
    $porEstado = ['PENDIENTE' => 0, 'VALIDADO' => 0, 'ANULADO' => 0];
    $porServicio = [];

    foreach ($rows as &$r) {
        $st = strtoupper(trim($r['Estado'] ?? 'PENDIENTE'));
        if (!isset($porEstado[$st])) {
            $porEstado[$st] = 0;
        }
        $porEstado[$st]++;

        $serv = $r['ServicioNombre'] ?? 'SIN ESPECIFICAR';
        $porServicio[$serv] = ($porServicio[$serv] ?? 0) + 1;

        $edad = null;
        if (!empty($r['FechaNacimiento'])) {
            $diff = $now->diff(new DateTime($r['FechaNacimiento']));
            $edad = $diff->y;
        }
        $r['Edad'] = $edad;
        $r['NombreCompleto'] = trim($r['ApellidoPaterno'] . ' ' . ($r['ApellidoMaterno'] ?? '') . ', ' . $r['Nombres']);
        $r['MedicoCompleto'] = trim(($r['MedicoPaterno'] ?? '') . ', ' . ($r['MedicoNombres'] ?? ''));
    }

    echo json_encode([
        'success' => true,
        'periodo' => [
            'inicio' => $fechaInicio,
            'fin'    => $fechaFin
        ],
        'resumen' => [
            'total_analisis'     => $totalAnalisis,
            'por_estado'         => $porEstado,
            'por_servicio'       => $porServicio
        ],
        'data' => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
