<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    // 1. TipoExamen
    $stmtTipo = $pdo->prepare("IF NOT EXISTS (SELECT 1 FROM laboratorio.TipoExamen WHERE Nombre = ?)
                               INSERT INTO laboratorio.TipoExamen (Nombre, Descripcion) VALUES (?, ?)");
    $tipos = [
        ['BIOQUIMICA', 'Perfil basal, lipidico, hepatico y quimica sanguinea'],
        ['MICROBIOLOGIA', 'Cultivos, antibiogramas y coloraciones Gram'],
        ['HEMATOLOGIA', 'Hemograma completo, coagulacion y grupo sanguineo'],
        ['URANALISIS', 'Examen fisico, quimico y microscopico de orina']
    ];
    foreach ($tipos as $t) {
        $stmtTipo->execute([$t[0], $t[0], $t[1]]);
    }

    // Obtener IDs de TipoExamen
    $mapTipo = [];
    foreach ($pdo->query("SELECT TipoExamenID, Nombre FROM laboratorio.TipoExamen")->fetchAll() as $row) {
        $mapTipo[$row['Nombre']] = $row['TipoExamenID'];
    }

    // 2. Unidades de Medida
    $stmtUni = $pdo->prepare("IF NOT EXISTS (SELECT 1 FROM laboratorio.UnidadMedida WHERE Codigo = ?)
                              INSERT INTO laboratorio.UnidadMedida (Codigo, Nombre, Simbolo) VALUES (?, ?, ?)");
    $unidades = [
        ['MG_DL', 'Miligramos por Decilitro', 'mg/dl'],
        ['G_DL', 'Gramos por Decilitro', 'g/dl'],
        ['UL', 'Unidades por Litro', 'U/L'],
        ['PORC', 'Porcentaje', '%'],
        ['SEG', 'Segundos', 'seg.'],
        ['MM3', 'Milimetro Cubico', '/mm³'],
        ['MMH', 'Milimetros por Hora', 'mm/h']
    ];
    foreach ($unidades as $u) {
        $stmtUni->execute([$u[0], $u[0], $u[1], $u[2]]);
    }

    $mapUni = [];
    foreach ($pdo->query("SELECT UnidadMedidaID, Simbolo FROM laboratorio.UnidadMedida")->fetchAll() as $row) {
        $mapUni[$row['Simbolo']] = $row['UnidadMedidaID'];
    }

    // 3. Examenes, Parametros y Valores de Referencia
    $catalog = [
        [
            'tipo' => 'BIOQUIMICA',
            'codigo' => 'EX_BASAL',
            'nombre' => 'PERFIL BASAL',
            'desc' => 'Perfil quimico basal metabolico y renal',
            'params' => [
                ['nombre' => 'GLUCOSA', 'unidad' => 'mg/dl', 'orden' => 1, 'refs' => [['texto' => '70 - 110 mg/dl', 'min' => 70, 'max' => 110]]],
                ['nombre' => 'UREA', 'unidad' => 'mg/dl', 'orden' => 2, 'refs' => [['texto' => '15 - 45 mg/dl', 'min' => 15, 'max' => 45]]],
                ['nombre' => 'CREATININA', 'unidad' => 'mg/dl', 'orden' => 3, 'refs' => [
                    ['texto' => 'Hombres: 0.9 - 1.3 mg/dl', 'min' => 0.9, 'max' => 1.3, 'sexo' => 'M'],
                    ['texto' => 'Mujeres: 0.6 - 1.1 mg/dl', 'min' => 0.6, 'max' => 1.1, 'sexo' => 'F']
                ]],
                ['nombre' => 'ACIDO URICO', 'unidad' => 'mg/dl', 'orden' => 4, 'refs' => [
                    ['texto' => 'Hombres: 2.5 - 7.2 mg/dl', 'min' => 2.5, 'max' => 7.2, 'sexo' => 'M'],
                    ['texto' => 'Mujeres: 2.5 - 6.0 mg/dl', 'min' => 2.5, 'max' => 6.0, 'sexo' => 'F']
                ]]
            ]
        ],
        [
            'tipo' => 'BIOQUIMICA',
            'codigo' => 'EX_LIPIDICO',
            'nombre' => 'PERFIL LIPIDICO',
            'desc' => 'Evaluacion de riesgo cardiovascular y lipidos',
            'params' => [
                ['nombre' => 'COLESTEROL TOTAL', 'unidad' => 'mg/dl', 'orden' => 1, 'refs' => [['texto' => '< 200 mg/dl (Deseable)', 'max' => 200]]],
                ['nombre' => 'HDL - COLESTEROL', 'unidad' => 'mg/dl', 'orden' => 2, 'refs' => [
                    ['texto' => 'Alto riesgo: < 40 mg/dl'],
                    ['texto' => 'Riesgo moderado: 40 - 59 mg/dl'],
                    ['texto' => 'Sin Riesgo: >= 60 mg/dl']
                ]],
                ['nombre' => 'LDL - COLESTEROL', 'unidad' => 'mg/dl', 'orden' => 3, 'refs' => [
                    ['texto' => 'Optimo: < 100 mg/dl'],
                    ['texto' => 'Elevado: 100 - 189 mg/dl'],
                    ['texto' => 'Muy alto: >= 190 mg/dl']
                ]],
                ['nombre' => 'VLDL - COLESTEROL', 'unidad' => 'mg/dl', 'orden' => 4, 'refs' => [
                    ['texto' => 'Optimo: < 150 mg/dl'],
                    ['texto' => 'Elevado: 150 - 499 mg/dl'],
                    ['texto' => 'Muy alto: >= 500 mg/dl']
                ]],
                ['nombre' => 'TRIGLICERIDOS', 'unidad' => 'mg/dl', 'orden' => 5, 'refs' => [
                    ['texto' => 'Optimo: < 150 mg/dl'],
                    ['texto' => 'Elevado: 150 - 499 mg/dl'],
                    ['texto' => 'Muy alto: >= 500 mg/dl']
                ]]
            ]
        ],
        [
            'tipo' => 'BIOQUIMICA',
            'codigo' => 'EX_HEPATICO',
            'nombre' => 'PERFIL HEPATICO',
            'desc' => 'Enzimas hepaticas y funcion biliar',
            'params' => [
                ['nombre' => 'PROTEINA TOTAL', 'unidad' => 'g/dl', 'orden' => 1, 'refs' => [['texto' => '6.1 - 7.9 g/dl', 'min' => 6.1, 'max' => 7.9]]],
                ['nombre' => 'ALBUMINA', 'unidad' => 'g/dl', 'orden' => 2, 'refs' => [['texto' => '3.5 - 4.8 g/dl', 'min' => 3.5, 'max' => 4.8]]],
                ['nombre' => 'GLOBULINA', 'unidad' => 'g/dl', 'orden' => 3, 'refs' => [['texto' => '1.2 - 2.2 g/dl', 'min' => 1.2, 'max' => 2.2]]],
                ['nombre' => 'TRANSAMINASA TGO', 'unidad' => 'U/L', 'orden' => 4, 'refs' => [
                    ['texto' => 'Hombres: < 38 U/L', 'sexo' => 'M', 'max' => 38],
                    ['texto' => 'Mujeres: < 32 U/L', 'sexo' => 'F', 'max' => 32]
                ]],
                ['nombre' => 'TRANSAMINASA TGP', 'unidad' => 'U/L', 'orden' => 5, 'refs' => [
                    ['texto' => 'Hombres: < 41 U/L', 'sexo' => 'M', 'max' => 41],
                    ['texto' => 'Mujeres: < 41 U/L', 'sexo' => 'F', 'max' => 31]
                ]],
                ['nombre' => 'BILIRRUBINA TOTAL', 'unidad' => 'mg/dl', 'orden' => 6, 'refs' => [['texto' => '< 1.0 mg/dl (Adultos) | Neonatos: 2.5 - 12.0 mg/dl']]],
                ['nombre' => 'BILIRRUBINA DIRECTA', 'unidad' => 'mg/dl', 'orden' => 7, 'refs' => [['texto' => '< 0.2 mg/dl', 'max' => 0.2]]],
                ['nombre' => 'BILIRRUBINA INDIRECTA', 'unidad' => 'mg/dl', 'orden' => 8, 'refs' => [['texto' => '< 0.8 mg/dl', 'max' => 0.8]]],
                ['nombre' => 'FOSFATASA ALCALINA', 'unidad' => 'U/L', 'orden' => 9, 'refs' => [['texto' => 'Adultos: 65 - 300 U/L | Niños/Adolescentes: < 645 U/L']]],
                ['nombre' => 'GAMMA GLUTAMIL TRANSPERASA (GGT)', 'unidad' => 'U/L', 'orden' => 10, 'refs' => [
                    ['texto' => 'Hombres: < 60 U/L', 'sexo' => 'M', 'max' => 60],
                    ['texto' => 'Mujeres: < 40 U/L', 'sexo' => 'F', 'max' => 40]
                ]],
                ['nombre' => 'TIEMPO DE PROTOMBINA', 'unidad' => 'seg.', 'orden' => 11, 'refs' => [['texto' => '12 - 14 seg', 'min' => 12, 'max' => 14]]],
                ['nombre' => 'INR', 'unidad' => '%', 'orden' => 12, 'refs' => [['texto' => '1.0 - 2.0 %', 'min' => 1.0, 'max' => 2.0]]]
            ]
        ],
        [
            'tipo' => 'BIOQUIMICA',
            'codigo' => 'EX_TOLERANCIA',
            'nombre' => 'TOLERANCIA A LA GLUCOSA',
            'desc' => 'Curva de tolerancia a la glucosa oral',
            'params' => [
                ['nombre' => 'GLUCOSA BASAL', 'unidad' => 'mg/dl', 'orden' => 1, 'refs' => [['texto' => '70 - 110 mg/dl', 'min' => 70, 'max' => 110]]],
                ['nombre' => 'GLUCOSA 1 HORA', 'unidad' => 'mg/dl', 'orden' => 2, 'refs' => [['texto' => '< 140 mg/dl', 'max' => 140]]],
                ['nombre' => 'GLUCOSA 2 HORAS', 'unidad' => 'mg/dl', 'orden' => 3, 'refs' => [['texto' => '< 200 mg/dl', 'max' => 200]]]
            ]
        ],
        [
            'tipo' => 'BIOQUIMICA',
            'codigo' => 'EX_ESPECIALES',
            'nombre' => 'OTROS EXAMENES BIOQUIMICOS',
            'desc' => 'Analisis enzimaticos y metabolicos complementarios',
            'params' => [
                ['nombre' => 'GLUCOSA POST PANDRIAL', 'unidad' => 'mg/dl', 'orden' => 1, 'refs' => [
                    ['texto' => 'Normal: < 140 mg/dl'],
                    ['texto' => 'Intolerancia: 140 - 200 mg/dl'],
                    ['texto' => 'Diabetes Mellitus: > 200 mg/dl']
                ]],
                ['nombre' => 'HEMOGLOBINA GLICOSILADA', 'unidad' => '%', 'orden' => 2, 'refs' => [
                    ['texto' => 'Normal: 4.0 - 5.7 %'],
                    ['texto' => 'Estable: 5.7 - 6.4 %'],
                    ['texto' => 'Elevado: >= 6.5 %']
                ]],
                ['nombre' => 'AMILASA', 'unidad' => 'U/L', 'orden' => 3, 'refs' => [['texto' => '< 125 U/L', 'max' => 125]]],
                ['nombre' => 'LIPASA', 'unidad' => 'U/L', 'orden' => 4, 'refs' => [['texto' => '13 - 60 U/L', 'min' => 13, 'max' => 60]]]
            ]
        ],
        [
            'tipo' => 'HEMATOLOGIA',
            'codigo' => 'EX_HEMATO',
            'nombre' => 'HEMATOLOGIA Y HEMOGRAMA',
            'desc' => 'Formula leucocitaria, serie roja y plaquetas',
            'params' => [
                ['nombre' => 'HEMOGLOBINA', 'unidad' => 'g/dl', 'orden' => 1, 'refs' => [
                    ['texto' => 'Hombres: 14.0 - 18.0 g/dl', 'sexo' => 'M', 'min' => 14, 'max' => 18],
                    ['texto' => 'Mujeres: 12.0 - 16.0 g/dl', 'sexo' => 'F', 'min' => 12, 'max' => 16]
                ]],
                ['nombre' => 'HEMATOCRITO', 'unidad' => '%', 'orden' => 2, 'refs' => [
                    ['texto' => 'Hombres: 40 - 54 %', 'sexo' => 'M', 'min' => 40, 'max' => 54],
                    ['texto' => 'Mujeres: 36 - 48 %', 'sexo' => 'F', 'min' => 36, 'max' => 48]
                ]],
                ['nombre' => 'LEUCOCITOS', 'unidad' => '/mm³', 'orden' => 3, 'refs' => [['texto' => '4,000 - 10,000 /mm³', 'min' => 4000, 'max' => 10000]]],
                ['nombre' => 'PLAQUETAS', 'unidad' => '/mm³', 'orden' => 4, 'refs' => [['texto' => '150,000 - 450,000 /mm³', 'min' => 150000, 'max' => 450000]]],
                ['nombre' => 'VELOCIDAD DE SEDIMENTACION (VSG)', 'unidad' => 'mm/h', 'orden' => 5, 'refs' => [
                    ['texto' => 'Hombres: < 15 mm/h', 'sexo' => 'M', 'max' => 15],
                    ['texto' => 'Mujeres: < 20 mm/h', 'sexo' => 'F', 'max' => 20]
                ]]
            ]
        ],
        [
            'tipo' => 'URANALISIS',
            'codigo' => 'EX_ORINA',
            'nombre' => 'URANALISIS Y SEDIMENTO SANTO',
            'desc' => 'Examen fisico, quimico y sedimento de orina',
            'params' => [
                ['nombre' => 'ASPECTO', 'unidad' => '', 'orden' => 1, 'refs' => [['texto' => 'Limpido / Transparente']]],
                ['nombre' => 'COLOR', 'unidad' => '', 'orden' => 2, 'refs' => [['texto' => 'Amarillo pajizo / Amarillo']]],
                ['nombre' => 'DENSIDAD', 'unidad' => '', 'orden' => 3, 'refs' => [['texto' => '1.005 - 1.030', 'min' => 1.005, 'max' => 1.030]]],
                ['nombre' => 'pH', 'unidad' => '', 'orden' => 4, 'refs' => [['texto' => '5.0 - 7.0', 'min' => 5.0, 'max' => 7.0]]],
                ['nombre' => 'PROTEINAS EN ORINA', 'unidad' => 'mg/dl', 'orden' => 5, 'refs' => [['texto' => 'Negativo (< 10 mg/dl)']]],
                ['nombre' => 'LEUCOCITOS EN SEDIMENTO', 'unidad' => '', 'orden' => 6, 'refs' => [['texto' => '0 - 5 por campo']]],
                ['nombre' => 'HEMATIES EN SEDIMENTO', 'unidad' => '', 'orden' => 7, 'refs' => [['texto' => '0 - 3 por campo']]]
            ]
        ]
    ];

    // Mapeo de SexoID
    $mapSexo = [];
    foreach ($pdo->query("SELECT SexoID, Codigo FROM catalogo.Sexo")->fetchAll() as $s) {
        $mapSexo[$s['Codigo']] = $s['SexoID'];
    }

    foreach ($catalog as $ex) {
        $tipoId = $mapTipo[$ex['tipo']] ?? 1;

        // Verificar o Insertar Examen
        $stmtExCheck = $pdo->prepare("SELECT ExamenID FROM laboratorio.Examen WHERE Codigo = ?");
        $stmtExCheck->execute([$ex['codigo']]);
        $exId = $stmtExCheck->fetchColumn();

        if (!$exId) {
            $stmtExIns = $pdo->prepare("INSERT INTO laboratorio.Examen (TipoExamenID, Codigo, Nombre, Descripcion, Estado) VALUES (?, ?, ?, ?, 1)");
            $stmtExIns->execute([$tipoId, $ex['codigo'], $ex['nombre'], $ex['desc']]);
            $exId = (int)$pdo->lastInsertId();
        }

        foreach ($ex['params'] as $p) {
            $uniId = isset($p['unidad']) && isset($mapUni[$p['unidad']]) ? $mapUni[$p['unidad']] : null;

            // Verificar o Insertar Parametro
            $stmtPCheck = $pdo->prepare("SELECT ParametroID FROM laboratorio.Parametro WHERE ExamenID = ? AND Nombre = ?");
            $stmtPCheck->execute([$exId, $p['nombre']]);
            $pId = $stmtPCheck->fetchColumn();

            if (!$pId) {
                $stmtPIns = $pdo->prepare("INSERT INTO laboratorio.Parametro (ExamenID, UnidadMedidaID, Nombre, Orden, Estado) VALUES (?, ?, ?, ?, 1)");
                $stmtPIns->execute([$exId, $uniId, $p['nombre'], $p['orden']]);
                $pId = (int)$pdo->lastInsertId();
            }

            // Insertar ValoresReferencia
            foreach ($p['refs'] as $r) {
                $sexoId = isset($r['sexo']) && isset($mapSexo[$r['sexo']]) ? $mapSexo[$r['sexo']] : null;
                $min = $r['min'] ?? null;
                $max = $r['max'] ?? null;
                $txt = $r['texto'] ?? null;

                $stmtRCheck = $pdo->prepare("SELECT ValorReferenciaID FROM laboratorio.ValorReferencia WHERE ParametroID = ? AND (TextoReferencia = ? OR TextoReferencia IS NULL)");
                $stmtRCheck->execute([$pId, $txt]);
                $refId = $stmtRCheck->fetchColumn();

                if (!$refId) {
                    $stmtRIns = $pdo->prepare("INSERT INTO laboratorio.ValorReferencia (ParametroID, SexoID, ValorMinimo, ValorMaximo, TextoReferencia, Estado) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmtRIns->execute([$pId, $sexoId, $min, $max, $txt]);
                }
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Catalogo poblado exitosamente en BD_HOSPITAL.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
