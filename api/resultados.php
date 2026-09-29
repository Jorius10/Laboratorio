<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Permite sobreescritura de metodo por parametro _method o header
    if ($method === 'POST' && isset($_GET['_method'])) {
        $method = strtoupper($_GET['_method']);
    }

    // ==========================================
    // GET: Listar o Cargar Resultados
    // ==========================================
    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';
        $solicitudId = isset($_GET['solicitud_id']) ? (int)$_GET['solicitud_id'] : 0;
        $resultadoId = isset($_GET['resultado_id']) ? (int)$_GET['resultado_id'] : 0;

        // Eliminar via GET si se solicita (facilidad de llamadas)
        if ($action === 'delete' && $solicitudId > 0) {
            deleteSolicitud($pdo, $solicitudId);
            echo json_encode(['success' => true, 'message' => "Solicitud #{$solicitudId} eliminada correctamente"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Cargar un informe especifico
        if ($solicitudId > 0 || $resultadoId > 0) {
            $whereClause = $solicitudId > 0 ? "s.SolicitudID = ?" : "r.ResultadoID = ?";
            $paramVal = $solicitudId > 0 ? $solicitudId : $resultadoId;

            $sql = "SELECT TOP 1 s.SolicitudID, s.PacienteID, s.PersonalSolicitanteID, s.ServicioSolicitanteID,
                           s.FechaSolicitud, s.Prioridad, s.Estado AS SolicitudEstado, s.Observacion AS SolicitudObservacion,
                           p.HistoriaClinica, per.NumeroDocumento, per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                           per.FechaNacimiento, per.SexoID,
                           COALESCE(sx.Codigo, CASE WHEN per.SexoID = 2 THEN 'F' WHEN per.SexoID = 1 THEN 'M' ELSE CAST(per.SexoID AS VARCHAR) END) AS SexoCodigo,
                           m.MuestraID, m.CodigoMuestra, m.FechaToma, m.FechaRecepcion, m.FechaProcesamiento,
                           serv.Nombre AS ServicioNombre,
                           docPer.Nombres AS MedicoNombres, docPer.ApellidoPaterno AS MedicoPaterno, docPer.ApellidoMaterno AS MedicoMaterno
                    FROM laboratorio.Solicitud s
                    INNER JOIN clinico.Paciente p ON s.PacienteID = p.PacienteID
                    INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                    LEFT JOIN catalogo.Sexo sx ON per.SexoID = sx.SexoID
                    LEFT JOIN organizacion.Servicio serv ON s.ServicioSolicitanteID = serv.ServicioID
                    LEFT JOIN rrhh.Personal doc ON s.PersonalSolicitanteID = doc.PersonalID
                    LEFT JOIN core.Persona docPer ON doc.PersonaID = docPer.PersonaID
                    LEFT JOIN laboratorio.Muestra m ON s.SolicitudID = m.SolicitudID
                    LEFT JOIN laboratorio.DetalleSolicitud ds ON s.SolicitudID = ds.SolicitudID
                    LEFT JOIN laboratorio.Resultado r ON ds.DetalleSolicitudID = r.DetalleSolicitudID
                    WHERE {$whereClause}";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$paramVal]);
            $header = $stmt->fetch();

            if (!$header) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Analisis no encontrado']);
                exit;
            }

            // Calcular edad
            $edad = null;
            if (!empty($header['FechaNacimiento'])) {
                $diff = (new DateTime())->diff(new DateTime($header['FechaNacimiento']));
                $edad = $diff->y;
            }
            $header['Edad'] = $edad;
            $header['Apellidos'] = trim($header['ApellidoPaterno'] . ' ' . ($header['ApellidoMaterno'] ?? ''));
            $header['PacienteNombreCompleto'] = trim($header['ApellidoPaterno'] . ' ' . ($header['ApellidoMaterno'] ?? '') . ', ' . $header['Nombres']);
            $header['MedicoNombreCompleto'] = trim(($header['MedicoPaterno'] ?? '') . ' ' . ($header['MedicoMaterno'] ?? '') . ', ' . ($header['MedicoNombres'] ?? ''));

            // Obtener todos los parametros de este reporte
            $sqlParams = "SELECT rp.ParametroID, rp.Valor, rp.Observacion, p.ExamenID, p.Nombre AS ParametroNombre,
                                 r.ResultadoID, r.FechaResultado, r.Observacion AS ResultadoObservacion, r.Estado AS ResultadoEstado
                          FROM laboratorio.DetalleSolicitud ds
                          INNER JOIN laboratorio.Resultado r ON ds.DetalleSolicitudID = r.DetalleSolicitudID
                          INNER JOIN laboratorio.ResultadoParametro rp ON r.ResultadoID = rp.ResultadoID
                          INNER JOIN laboratorio.Parametro p ON rp.ParametroID = p.ParametroID
                          WHERE ds.SolicitudID = ?";
            $stmtP = $pdo->prepare($sqlParams);
            $stmtP->execute([$header['SolicitudID']]);
            $paramsRows = $stmtP->fetchAll();

            $valoresMap = [];
            $observacionesFinal = '';
            $fechaEmision = null;

            foreach ($paramsRows as $row) {
                $valoresMap[(int)$row['ParametroID']] = $row['Valor'];
                if (!empty($row['ResultadoObservacion'])) {
                    $observacionesFinal = $row['ResultadoObservacion'];
                }
                if (!empty($row['FechaResultado'])) {
                    $fechaEmision = $row['FechaResultado'];
                }
            }

            $header['Valores'] = $valoresMap;
            $header['FechaEmision'] = $fechaEmision ?? $header['FechaSolicitud'];
            $header['Observaciones'] = $observacionesFinal;

            echo json_encode(['success' => true, 'data' => $header], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Listar analisis historicos recientes
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
        $q = trim($_GET['q'] ?? '');

        $sqlList = "SELECT TOP {$limit} s.SolicitudID, s.FechaSolicitud, s.Prioridad, s.Estado, s.Observacion AS Diagnostico,
                           m.CodigoMuestra, m.FechaToma,
                           p.PacienteID, p.HistoriaClinica, per.NumeroDocumento, per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                           serv.Nombre AS ServicioNombre,
                           docPer.Nombres AS MedicoNombres, docPer.ApellidoPaterno AS MedicoPaterno,
                           COUNT(rp.ResultadoParametroID) AS TotalParametros
                    FROM laboratorio.Solicitud s
                    INNER JOIN clinico.Paciente p ON s.PacienteID = p.PacienteID
                    INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                    LEFT JOIN organizacion.Servicio serv ON s.ServicioSolicitanteID = serv.ServicioID
                    LEFT JOIN rrhh.Personal doc ON s.PersonalSolicitanteID = doc.PersonalID
                    LEFT JOIN core.Persona docPer ON doc.PersonaID = docPer.PersonaID
                    LEFT JOIN laboratorio.Muestra m ON s.SolicitudID = m.SolicitudID
                    LEFT JOIN laboratorio.DetalleSolicitud ds ON s.SolicitudID = ds.SolicitudID
                    LEFT JOIN laboratorio.Resultado r ON ds.DetalleSolicitudID = r.DetalleSolicitudID
                    LEFT JOIN laboratorio.ResultadoParametro rp ON r.ResultadoID = rp.ResultadoID";

        $params = [];
        if ($q !== '') {
            $sqlList .= " WHERE per.NumeroDocumento LIKE ? OR p.HistoriaClinica LIKE ? OR per.ApellidoPaterno LIKE ? OR per.Nombres LIKE ? OR s.CodigoSolicitud LIKE ? OR m.CodigoMuestra LIKE ?";
            $term = "%{$q}%";
            $params = [$term, $term, $term, $term, $term, $term];
        }

        $sqlList .= " GROUP BY s.SolicitudID, s.FechaSolicitud, s.Prioridad, s.Estado, s.Observacion,
                               m.CodigoMuestra, m.FechaToma, p.PacienteID, p.HistoriaClinica, per.NumeroDocumento,
                               per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno, serv.Nombre,
                               docPer.Nombres, docPer.ApellidoPaterno
                      ORDER BY s.SolicitudID DESC";

        $stmtList = $pdo->prepare($sqlList);
        $stmtList->execute($params);
        $list = $stmtList->fetchAll();

        foreach ($list as &$item) {
            $item['PacienteNombre'] = trim($item['ApellidoPaterno'] . ' ' . ($item['ApellidoMaterno'] ?? '') . ', ' . $item['Nombres']);
            $item['MedicoNombre'] = trim(($item['MedicoPaterno'] ?? '') . ', ' . ($item['MedicoNombres'] ?? ''));
        }

        echo json_encode(['success' => true, 'data' => $list], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================
    // POST / PUT: Guardar o Actualizar Analisis
    // ==========================================
    if ($method === 'POST' || $method === 'PUT') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos JSON invalidos']);
            exit;
        }

        $action = $body['action'] ?? ($method === 'PUT' ? 'update' : 'create');
        $solicitudId = (int)($body['solicitud_id'] ?? 0);

        // Actualizar solo el estado
        if ($action === 'update_status' && $solicitudId > 0) {
            $nuevoEstado = strtoupper(trim($body['estado'] ?? 'VALIDADO'));
            $allowed = ['PENDIENTE', 'VALIDADO', 'ANULADO'];
            if (!in_array($nuevoEstado, $allowed, true)) {
                $nuevoEstado = 'VALIDADO';
            }

            $stmtUp = $pdo->prepare("UPDATE laboratorio.Solicitud SET Estado = ? WHERE SolicitudID = ?");
            $stmtUp->execute([$nuevoEstado, $solicitudId]);

            // Actualizar tambien en Resultado
            $stmtUpR = $pdo->prepare("UPDATE r SET r.Estado = ? FROM laboratorio.Resultado r
                                      INNER JOIN laboratorio.DetalleSolicitud ds ON r.DetalleSolicitudID = ds.DetalleSolicitudID
                                      WHERE ds.SolicitudID = ?");
            $stmtUpR->execute([$nuevoEstado, $solicitudId]);

            echo json_encode(['success' => true, 'message' => "Estado de Solicitud #{$solicitudId} actualizado a {$nuevoEstado}"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $pacienteId = (int)($body['paciente_id'] ?? 0);
        $medicoId = (int)($body['personal_solicitante_id'] ?? 1);
        $servicioId = (int)($body['servicio_solicitante_id'] ?? 1);
        $fechaToma = !empty($body['fecha_toma']) ? $body['fecha_toma'] : date('Y-m-d H:i:s');
        $fechaEmision = !empty($body['fecha_emision']) ? $body['fecha_emision'] : date('Y-m-d H:i:s');
        $diagnostico = trim($body['diagnostico_presuntivo'] ?? '');
        $observaciones = trim($body['observaciones'] ?? '');
        $otrosExamenes = trim($body['otros_examenes'] ?? '');
        $estadoSolicitud = strtoupper(trim($body['estado'] ?? 'VALIDADO'));
        $valores = (array)($body['valores'] ?? []);

        if ($pacienteId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar o registrar un paciente']);
            exit;
        }

        $obsSolicitud = $diagnostico;
        if ($otrosExamenes !== '') {
            $obsSolicitud .= ($obsSolicitud !== '' ? ' | Otros: ' : 'Otros: ') . $otrosExamenes;
        }

        // Obtener catalogo de parametros para mapeo
        $stmtParams = $pdo->query("SELECT ParametroID, ExamenID FROM laboratorio.Parametro WHERE Estado = 1");
        $allParams = $stmtParams->fetchAll();
        $paramToExamen = [];
        foreach ($allParams as $row) {
            $paramToExamen[(int)$row['ParametroID']] = (int)$row['ExamenID'];
        }

        $examenesAfectados = [];
        foreach ($valores as $pId => $val) {
            $pId = (int)$pId;
            $valStr = trim((string)$val);
            if ($valStr !== '' && isset($paramToExamen[$pId])) {
                $exId = $paramToExamen[$pId];
                $examenesAfectados[$exId][$pId] = $valStr;
            }
        }
        if (empty($examenesAfectados)) {
            $examenesAfectados[1] = [];
        }

        $pdo->beginTransaction();

        if ($solicitudId > 0 && ($action === 'update' || $method === 'PUT')) {
            // ==========================================
            // ACTUALIZACION DE SOLICITUD EXISTENTE
            // ==========================================
            $sqlUp = "UPDATE laboratorio.Solicitud
                      SET PacienteID = ?, PersonalSolicitanteID = ?, ServicioSolicitanteID = ?,
                          FechaSolicitud = ?, Estado = ?, Observacion = ?
                      WHERE SolicitudID = ?";
            $stmtUp = $pdo->prepare($sqlUp);
            $stmtUp->execute([$pacienteId, $medicoId, $servicioId, $fechaToma, $estadoSolicitud, $obsSolicitud, $solicitudId]);

            // Actualizar Muestra
            $sqlUpM = "UPDATE laboratorio.Muestra
                       SET FechaToma = ?, FechaRecepcion = ?, FechaProcesamiento = ?, Estado = ?
                       WHERE SolicitudID = ?";
            $stmtUpM = $pdo->prepare($sqlUpM);
            $stmtUpM->execute([$fechaToma, $fechaToma, $fechaEmision, $estadoSolicitud === 'ANULADO' ? 'ANULADA' : 'PROCESADA', $solicitudId]);

            // Obtener codigo de muestra existente
            $stmtCod = $pdo->prepare("SELECT CodigoMuestra FROM laboratorio.Muestra WHERE SolicitudID = ?");
            $stmtCod->execute([$solicitudId]);
            $codigoMuestra = $stmtCod->fetchColumn() ?: 'LAB-' . date('Ymd') . '-' . str_pad((string)$solicitudId, 4, '0', STR_PAD_LEFT);

            // Eliminar detalles previos para reinsertar los nuevos valores actualizados
            $stmtDetIds = $pdo->prepare("SELECT DetalleSolicitudID FROM laboratorio.DetalleSolicitud WHERE SolicitudID = ?");
            $stmtDetIds->execute([$solicitudId]);
            $detIds = $stmtDetIds->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($detIds)) {
                $inQuery = implode(',', array_map('intval', $detIds));
                $stmtResIds = $pdo->query("SELECT ResultadoID FROM laboratorio.Resultado WHERE DetalleSolicitudID IN ({$inQuery})");
                $resIds = $stmtResIds->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($resIds)) {
                    $inRes = implode(',', array_map('intval', $resIds));
                    $pdo->exec("DELETE FROM laboratorio.ResultadoParametro WHERE ResultadoID IN ({$inRes})");
                    $pdo->exec("DELETE FROM laboratorio.Resultado WHERE ResultadoID IN ({$inRes})");
                }
                $pdo->exec("DELETE FROM laboratorio.DetalleSolicitud WHERE SolicitudID = {$solicitudId}");
            }

            // Re-insertar detalles y resultados
            $insDetalle = $pdo->prepare("INSERT INTO laboratorio.DetalleSolicitud (SolicitudID, ExamenID, Estado) VALUES (?, ?, ?)");
            $insResultado = $pdo->prepare("INSERT INTO laboratorio.Resultado (DetalleSolicitudID, ProfesionalResponsableID, FechaResultado, Estado, Observacion) VALUES (?, ?, ?, ?, ?)");
            $insParam = $pdo->prepare("INSERT INTO laboratorio.ResultadoParametro (ResultadoID, ParametroID, Valor, Observacion) VALUES (?, ?, ?, NULL)");

            $totalParametrosGuardados = 0;
            foreach ($examenesAfectados as $exId => $paramsOfExam) {
                $insDetalle->execute([$solicitudId, $exId, $estadoSolicitud]);
                $detalleId = (int)$pdo->lastInsertId();

                $insResultado->execute([$detalleId, $medicoId, $fechaEmision, $estadoSolicitud, $observaciones]);
                $resultadoId = (int)$pdo->lastInsertId();

                foreach ($paramsOfExam as $pId => $v) {
                    $insParam->execute([$resultadoId, $pId, $v]);
                    $totalParametrosGuardados++;
                }
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Solicitud #{$solicitudId} ({$codigoMuestra}) actualizada exitosamente",
                'data' => [
                    'SolicitudID' => $solicitudId,
                    'CodigoMuestra' => $codigoMuestra,
                    'FechaToma' => $fechaToma,
                    'FechaEmision' => $fechaEmision,
                    'Estado' => $estadoSolicitud,
                    'TotalParametros' => $totalParametrosGuardados
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;

        } else {
            // ==========================================
            // CREACION DE NUEVA SOLICITUD
            // ==========================================
            $sqlSol = "INSERT INTO laboratorio.Solicitud (PacienteID, PersonalSolicitanteID, ServicioSolicitanteID, FechaSolicitud, Prioridad, Estado, Observacion)
                       VALUES (?, ?, ?, ?, 'NORMAL', ?, ?)";
            $stmtSol = $pdo->prepare($sqlSol);
            $stmtSol->execute([$pacienteId, $medicoId, $servicioId, $fechaToma, $estadoSolicitud, $obsSolicitud]);
            $solicitudId = (int)$pdo->lastInsertId();

            $codigoMuestra = 'LAB-' . date('Ymd') . '-' . str_pad((string)$solicitudId, 4, '0', STR_PAD_LEFT);
            $sqlMuestra = "INSERT INTO laboratorio.Muestra (SolicitudID, TipoMuestraID, CodigoMuestra, FechaToma, FechaRecepcion, FechaProcesamiento, Estado)
                           VALUES (?, 1, ?, ?, ?, ?, 'PROCESADA')";
            $stmtMuestra = $pdo->prepare($sqlMuestra);
            $stmtMuestra->execute([$solicitudId, $codigoMuestra, $fechaToma, $fechaToma, $fechaEmision]);

            $insDetalle = $pdo->prepare("INSERT INTO laboratorio.DetalleSolicitud (SolicitudID, ExamenID, Estado) VALUES (?, ?, ?)");
            $insResultado = $pdo->prepare("INSERT INTO laboratorio.Resultado (DetalleSolicitudID, ProfesionalResponsableID, FechaResultado, Estado, Observacion) VALUES (?, ?, ?, ?, ?)");
            $insParam = $pdo->prepare("INSERT INTO laboratorio.ResultadoParametro (ResultadoID, ParametroID, Valor, Observacion) VALUES (?, ?, ?, NULL)");

            $totalParametrosGuardados = 0;
            foreach ($examenesAfectados as $exId => $paramsOfExam) {
                $insDetalle->execute([$solicitudId, $exId, $estadoSolicitud]);
                $detalleId = (int)$pdo->lastInsertId();

                $insResultado->execute([$detalleId, $medicoId, $fechaEmision, $estadoSolicitud, $observaciones]);
                $resultadoId = (int)$pdo->lastInsertId();

                foreach ($paramsOfExam as $pId => $v) {
                    $insParam->execute([$resultadoId, $pId, $v]);
                    $totalParametrosGuardados++;
                }
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Informe de laboratorio guardado exitosamente en BD_HOSPITAL',
                'data' => [
                    'SolicitudID' => $solicitudId,
                    'CodigoMuestra' => $codigoMuestra,
                    'FechaToma' => $fechaToma,
                    'FechaEmision' => $fechaEmision,
                    'Estado' => $estadoSolicitud,
                    'TotalParametros' => $totalParametrosGuardados
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // ==========================================
    // DELETE: Eliminar Solicitud Completa
    // ==========================================
    if ($method === 'DELETE') {
        $solicitudId = isset($_GET['solicitud_id']) ? (int)$_GET['solicitud_id'] : 0;
        if ($solicitudId <= 0) {
            $body = json_decode(file_get_contents('php://input'), true);
            $solicitudId = (int)($body['solicitud_id'] ?? 0);
        }

        if ($solicitudId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de solicitud invalido para eliminacion']);
            exit;
        }

        deleteSolicitud($pdo, $solicitudId);

        echo json_encode([
            'success' => true,
            'message' => "Solicitud #{$solicitudId} eliminada exitosamente de la base de datos"
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

/**
 * Funcion auxiliar para eliminar solicitud y todos sus registros hijos en cascada
 */
function deleteSolicitud(PDO $pdo, int $solicitudId): void
{
    $pdo->beginTransaction();
    try {
        $stmtDetIds = $pdo->prepare("SELECT DetalleSolicitudID FROM laboratorio.DetalleSolicitud WHERE SolicitudID = ?");
        $stmtDetIds->execute([$solicitudId]);
        $detIds = $stmtDetIds->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($detIds)) {
            $inQuery = implode(',', array_map('intval', $detIds));
            $stmtResIds = $pdo->query("SELECT ResultadoID FROM laboratorio.Resultado WHERE DetalleSolicitudID IN ({$inQuery})");
            $resIds = $stmtResIds->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($resIds)) {
                $inRes = implode(',', array_map('intval', $resIds));
                $pdo->exec("DELETE FROM laboratorio.ResultadoParametro WHERE ResultadoID IN ({$inRes})");
                $pdo->exec("DELETE FROM laboratorio.Resultado WHERE ResultadoID IN ({$inRes})");
            }
            $pdo->exec("DELETE FROM laboratorio.DetalleSolicitud WHERE SolicitudID = {$solicitudId}");
        }

        $pdo->exec("DELETE FROM laboratorio.Muestra WHERE SolicitudID = {$solicitudId}");
        $pdo->exec("DELETE FROM laboratorio.Solicitud WHERE SolicitudID = {$solicitudId}");

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
