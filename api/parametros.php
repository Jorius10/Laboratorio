<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    // 1. Obtener examenes
    $sqlExamenes = "SELECT ExamenID, Codigo, Nombre, Descripcion
                    FROM laboratorio.Examen
                    WHERE Estado = 1
                    ORDER BY ExamenID ASC";
    $stmtEx = $pdo->query($sqlExamenes);
    $examenes = $stmtEx->fetchAll();

    // 2. Obtener parametros con unidad
    $sqlParams = "SELECT p.ParametroID, p.ExamenID, p.Nombre, p.TipoDato, p.Orden,
                         u.UnidadMedidaID, u.Codigo AS UnidadCodigo, u.Simbolo AS UnidadSimbolo
                  FROM laboratorio.Parametro p
                  LEFT JOIN laboratorio.UnidadMedida u ON p.UnidadMedidaID = u.UnidadMedidaID
                  WHERE p.Estado = 1
                  ORDER BY p.ExamenID, p.Orden";
    $stmtParams = $pdo->query($sqlParams);
    $parametros = $stmtParams->fetchAll();

    // 3. Obtener valores de referencia
    $sqlRef = "SELECT vr.ValorReferenciaID, vr.ParametroID, vr.SexoID, s.Codigo AS SexoCodigo,
                      vr.EdadMinima, vr.EdadMaxima, vr.ValorMinimo, vr.ValorMaximo, vr.TextoReferencia
               FROM laboratorio.ValorReferencia vr
               LEFT JOIN catalogo.Sexo s ON vr.SexoID = s.SexoID
               WHERE vr.Estado = 1";
    $stmtRef = $pdo->query($sqlRef);
    $referencias = $stmtRef->fetchAll();

    $refsByParam = [];
    foreach ($referencias as $r) {
        $refsByParam[$r['ParametroID']][] = [
            'ValorReferenciaID' => $r['ValorReferenciaID'],
            'SexoID'            => $r['SexoID'],
            'SexoCodigo'        => $r['SexoCodigo'],
            'EdadMinima'        => $r['EdadMinima'] !== null ? (float)$r['EdadMinima'] : null,
            'EdadMaxima'        => $r['EdadMaxima'] !== null ? (float)$r['EdadMaxima'] : null,
            'ValorMinimo'       => $r['ValorMinimo'] !== null ? (float)$r['ValorMinimo'] : null,
            'ValorMaximo'       => $r['ValorMaximo'] !== null ? (float)$r['ValorMaximo'] : null,
            'TextoReferencia'   => $r['TextoReferencia']
        ];
    }

    $paramsByExamen = [];
    $allParamsMap = [];

    foreach ($parametros as $p) {
        $pId = $p['ParametroID'];
        $item = [
            'ParametroID'     => $pId,
            'ExamenID'        => $p['ExamenID'],
            'Nombre'          => $p['Nombre'],
            'TipoDato'        => $p['TipoDato'],
            'Orden'           => $p['Orden'],
            'UnidadMedidaID'  => $p['UnidadMedidaID'],
            'UnidadSimbolo'   => $p['UnidadSimbolo'],
            'ValoresReferencia' => $refsByParam[$pId] ?? []
        ];
        $paramsByExamen[$p['ExamenID']][] = $item;
        $allParamsMap[$pId] = $item;
    }

    foreach ($examenes as &$e) {
        $e['Parametros'] = $paramsByExamen[$e['ExamenID']] ?? [];
    }

    echo json_encode([
        'success' => true,
        'examenes' => $examenes,
        'parametros_map' => $allParamsMap
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
