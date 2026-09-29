<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $q = trim($_GET['q'] ?? '');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $sql = "SELECT p.PacienteID, p.PersonaID, p.HistoriaClinica, per.TipoDocumentoID,
                           per.NumeroDocumento, per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                           per.FechaNacimiento, per.SexoID,
                           COALESCE(s.Codigo, CASE WHEN per.SexoID = 2 THEN 'F' WHEN per.SexoID = 1 THEN 'M' ELSE CAST(per.SexoID AS VARCHAR) END) AS SexoCodigo,
                           COALESCE(s.Nombre, CASE WHEN per.SexoID = 2 THEN 'Femenino' WHEN per.SexoID = 1 THEN 'Masculino' ELSE 'No especificado' END) AS SexoNombre
                    FROM clinico.Paciente p
                    INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                    LEFT JOIN catalogo.Sexo s ON per.SexoID = s.SexoID
                    WHERE p.PacienteID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $patient = $stmt->fetch();

            if (!$patient) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Paciente no encontrado']);
                exit;
            }

            // Calcular edad
            $edad = null;
            if (!empty($patient['FechaNacimiento'])) {
                $dob = new DateTime($patient['FechaNacimiento']);
                $now = new DateTime();
                $diff = $now->diff($dob);
                $edad = $diff->y;
            }

            $patient['Edad'] = $edad;
            $patient['NombreCompleto'] = trim($patient['ApellidoPaterno'] . ' ' . ($patient['ApellidoMaterno'] ?? '') . ', ' . $patient['Nombres']);

            echo json_encode(['success' => true, 'data' => $patient], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $historialPacienteId = isset($_GET['historial_paciente_id']) ? (int)$_GET['historial_paciente_id'] : 0;
        if ($historialPacienteId > 0) {
            $sqlH = "SELECT s.SolicitudID, s.CodigoSolicitud, s.FechaSolicitud, s.Prioridad, s.Estado,
                            s.DiagnosticoPresuntivo, serv.Nombre AS ServicioNombre,
                            docPer.Nombres AS MedicoNombres, docPer.ApellidoPaterno AS MedicoPaterno,
                            (SELECT COUNT(*) FROM laboratorio.DetalleSolicitud ds WHERE ds.SolicitudID = s.SolicitudID) as TotalExamenes
                     FROM laboratorio.Solicitud s
                     LEFT JOIN organizacion.Servicio serv ON s.ServicioSolicitanteID = serv.ServicioID
                     LEFT JOIN rrhh.Personal doc ON s.PersonalSolicitanteID = doc.PersonalID
                     LEFT JOIN core.Persona docPer ON doc.PersonaID = docPer.PersonaID
                     WHERE s.PacienteID = ?
                     ORDER BY s.FechaSolicitud DESC";
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([$historialPacienteId]);
            $historial = $stmtH->fetchAll();

            foreach ($historial as &$h) {
                $h['MedicoCompleto'] = trim(($h['MedicoPaterno'] ?? '') . ', ' . ($h['MedicoNombres'] ?? ''));
            }

            echo json_encode(['success' => true, 'data' => $historial], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Busqueda general o lista de pacientes recientes
        if ($q !== '') {
            $sql = "SELECT TOP 15 p.PacienteID, p.PersonaID, p.HistoriaClinica, per.NumeroDocumento,
                           per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                           per.FechaNacimiento, per.SexoID,
                           COALESCE(s.Codigo, CASE WHEN per.SexoID = 2 THEN 'F' WHEN per.SexoID = 1 THEN 'M' ELSE CAST(per.SexoID AS VARCHAR) END) AS SexoCodigo,
                           COALESCE(s.Nombre, CASE WHEN per.SexoID = 2 THEN 'Femenino' WHEN per.SexoID = 1 THEN 'Masculino' ELSE 'No especificado' END) AS SexoNombre
                    FROM clinico.Paciente p
                    INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                    LEFT JOIN catalogo.Sexo s ON per.SexoID = s.SexoID
                    WHERE per.NumeroDocumento LIKE ?
                       OR p.HistoriaClinica LIKE ?
                       OR per.Nombres LIKE ?
                       OR per.ApellidoPaterno LIKE ?
                       OR per.ApellidoMaterno LIKE ?
                    ORDER BY per.ApellidoPaterno, per.Nombres";
            $searchTerm = "%{$q}%";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        } else {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
            $sql = "SELECT TOP {$limit} p.PacienteID, p.PersonaID, p.HistoriaClinica, per.NumeroDocumento,
                           per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                           per.FechaNacimiento, per.SexoID,
                           COALESCE(s.Codigo, CASE WHEN per.SexoID = 2 THEN 'F' WHEN per.SexoID = 1 THEN 'M' ELSE CAST(per.SexoID AS VARCHAR) END) AS SexoCodigo,
                           COALESCE(s.Nombre, CASE WHEN per.SexoID = 2 THEN 'Femenino' WHEN per.SexoID = 1 THEN 'Masculino' ELSE 'No especificado' END) AS SexoNombre
                    FROM clinico.Paciente p
                    INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                    LEFT JOIN catalogo.Sexo s ON per.SexoID = s.SexoID
                    ORDER BY per.ApellidoPaterno, per.Nombres";
            $stmt = $pdo->query($sql);
        }

        $patients = $stmt->fetchAll();
        $now = new DateTime();
        foreach ($patients as &$p) {
            $edad = null;
            if (!empty($p['FechaNacimiento'])) {
                $dob = new DateTime($p['FechaNacimiento']);
                $diff = $now->diff($dob);
                $edad = $diff->y;
            }
            $p['Edad'] = $edad;
            $p['NombreCompleto'] = trim($p['ApellidoPaterno'] . ' ' . ($p['ApellidoMaterno'] ?? '') . ', ' . $p['Nombres']);
        }

        echo json_encode(['success' => true, 'data' => $patients], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos JSON invalidos']);
            exit;
        }

        $dni = trim($body['NumeroDocumento'] ?? '');
        $nombres = trim($body['Nombres'] ?? '');
        $apellidoPaterno = trim($body['ApellidoPaterno'] ?? '');
        $apellidoMaterno = trim($body['ApellidoMaterno'] ?? '');
        $sexoCodigo = strtoupper(trim($body['SexoCodigo'] ?? 'M'));
        $edad = isset($body['Edad']) && $body['Edad'] !== '' ? (int)$body['Edad'] : null;
        $historia = trim($body['HistoriaClinica'] ?? '');

        if ($dni === '' || $nombres === '' || $apellidoPaterno === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'DNI, Nombres y Apellido Paterno son obligatorios']);
            exit;
        }

        $sexoID = ($sexoCodigo === 'F') ? 2 : 1;
        $tipoDocID = 1; // DNI

        // Calcular fecha estimada de nacimiento si solo se dio edad
        $fechaNacimiento = null;
        if ($edad !== null && $edad >= 0) {
            $year = (int)date('Y') - $edad;
            $fechaNacimiento = sprintf('%04d-01-01', $year);
        }

        $pdo->beginTransaction();

        // Verificar si existe persona con este DNI
        $checkStmt = $pdo->prepare("SELECT PersonaID FROM core.Persona WHERE NumeroDocumento = ?");
        $checkStmt->execute([$dni]);
        $persona = $checkStmt->fetch();

        if ($persona) {
            $personaID = (int)$persona['PersonaID'];
            // Actualizar datos de persona
            $upd = $pdo->prepare("UPDATE core.Persona SET Nombres = ?, ApellidoPaterno = ?, ApellidoMaterno = ?, SexoID = ?, FechaNacimiento = COALESCE(?, FechaNacimiento), FechaActualizacion = SYSDATETIME() WHERE PersonaID = ?");
            $upd->execute([$nombres, $apellidoPaterno, $apellidoMaterno, $sexoID, $fechaNacimiento, $personaID]);
        } else {
            $insPersona = $pdo->prepare("INSERT INTO core.Persona (TipoDocumentoID, NumeroDocumento, Nombres, ApellidoPaterno, ApellidoMaterno, FechaNacimiento, SexoID) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insPersona->execute([$tipoDocID, $dni, $nombres, $apellidoPaterno, $apellidoMaterno, $fechaNacimiento, $sexoID]);
            $personaID = (int)$pdo->lastInsertId();
        }

        // Verificar paciente
        $checkPac = $pdo->prepare("SELECT PacienteID FROM clinico.Paciente WHERE PersonaID = ?");
        $checkPac->execute([$personaID]);
        $paciente = $checkPac->fetch();

        if ($paciente) {
            $pacienteID = (int)$paciente['PacienteID'];
            if ($historia !== '') {
                $updPac = $pdo->prepare("UPDATE clinico.Paciente SET HistoriaClinica = ? WHERE PacienteID = ?");
                $updPac->execute([$historia, $pacienteID]);
            }
        } else {
            if ($historia === '') {
                $historia = 'HC-' . str_pad((string)$personaID, 6, '0', STR_PAD_LEFT);
            }
            $insPac = $pdo->prepare("INSERT INTO clinico.Paciente (PersonaID, HistoriaClinica) VALUES (?, ?)");
            $insPac->execute([$personaID, $historia]);
            $pacienteID = (int)$pdo->lastInsertId();
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Paciente registrado o actualizado exitosamente',
            'data' => [
                'PacienteID' => $pacienteID,
                'PersonaID' => $personaID,
                'NumeroDocumento' => $dni,
                'Nombres' => $nombres,
                'ApellidoPaterno' => $apellidoPaterno,
                'ApellidoMaterno' => $apellidoMaterno,
                'NombreCompleto' => trim($apellidoPaterno . ' ' . $apellidoMaterno . ', ' . $nombres),
                'SexoCodigo' => $sexoCodigo,
                'Edad' => $edad,
                'HistoriaClinica' => $historia
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
