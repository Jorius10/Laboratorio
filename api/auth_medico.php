<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

try {
    $pdo = Database::getConnection();

    // 1. Obtener lista de medicos para sugerencias en el portal
    if ($method === 'GET' && ($action === 'list' || isset($_GET['list']))) {
        $sql = "SELECT p.PersonalID, per.PersonaID, per.NumeroDocumento,
                       per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                       per.Correo, c.Nombre AS Cargo, esp.Nombre AS Especialidad, prof.Nombre AS Profesion
                FROM rrhh.Personal p
                INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                LEFT JOIN rrhh.AsignacionPersonal ap ON p.PersonalID = ap.PersonalID AND ap.EsActual = 1
                LEFT JOIN rrhh.Cargo c ON ap.CargoID = c.CargoID
                LEFT JOIN rrhh.Especialidad esp ON ap.EspecialidadID = esp.EspecialidadID
                LEFT JOIN rrhh.Profesion prof ON ap.ProfesionID = prof.ProfesionID
                WHERE p.Estado = 1 AND per.Correo IS NOT NULL AND per.Correo <> ''
                ORDER BY per.ApellidoPaterno, per.Nombres";

        $stmt = $pdo->query($sql);
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($medicos as &$m) {
            $m['NombreCompleto'] = trim($m['ApellidoPaterno'] . ' ' . ($m['ApellidoMaterno'] ?? '') . ', ' . $m['Nombres']);
            $m['EspecialidadOcupacion'] = $m['Especialidad'] ?: ($m['Cargo'] ?: 'Personal Médico');
            $m['ModulosAutorizados'] = getModulosPorEspecialidad($m['EspecialidadOcupacion'], (string)$m['Cargo']);
        }

        echo json_encode(['success' => true, 'data' => $medicos], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Verificar sesion activa
    if ($method === 'GET' && $action === 'check') {
        if (!empty($_SESSION['medico_autenticado'])) {
            echo json_encode(['success' => true, 'authenticated' => true, 'medico' => $_SESSION['medico_autenticado']], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => true, 'authenticated' => false], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 3. Cerrar sesion
    if ($method === 'POST' && $action === 'logout') {
        unset($_SESSION['medico_autenticado']);
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión finalizada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Iniciar sesion / Validar por correo
    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: $_POST;
        $correo = strtolower(trim($body['correo'] ?? ''));

        if ($correo === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ingrese el correo electrónico del médico'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $sql = "SELECT p.PersonalID, per.PersonaID, per.NumeroDocumento,
                       per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                       per.Correo, per.Celular, c.Nombre AS Cargo, esp.Nombre AS Especialidad, prof.Nombre AS Profesion
                FROM rrhh.Personal p
                INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                LEFT JOIN rrhh.AsignacionPersonal ap ON p.PersonalID = ap.PersonalID AND ap.EsActual = 1
                LEFT JOIN rrhh.Cargo c ON ap.CargoID = c.CargoID
                LEFT JOIN rrhh.Especialidad esp ON ap.EspecialidadID = esp.EspecialidadID
                LEFT JOIN rrhh.Profesion prof ON ap.ProfesionID = prof.ProfesionID
                WHERE p.Estado = 1 AND LOWER(LTRIM(RTRIM(per.Correo))) = LOWER(?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$correo]);
        $medico = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$medico) {
            // Busqueda alternativa por DNI si ingreso DNI
            $sqlDni = "SELECT p.PersonalID, per.PersonaID, per.NumeroDocumento,
                              per.Nombres, per.ApellidoPaterno, per.ApellidoMaterno,
                              per.Correo, per.Celular, c.Nombre AS Cargo, esp.Nombre AS Especialidad, prof.Nombre AS Profesion
                       FROM rrhh.Personal p
                       INNER JOIN core.Persona per ON p.PersonaID = per.PersonaID
                       LEFT JOIN rrhh.AsignacionPersonal ap ON p.PersonalID = ap.PersonalID AND ap.EsActual = 1
                       LEFT JOIN rrhh.Cargo c ON ap.CargoID = c.CargoID
                       LEFT JOIN rrhh.Especialidad esp ON ap.EspecialidadID = esp.EspecialidadID
                       LEFT JOIN rrhh.Profesion prof ON ap.ProfesionID = prof.ProfesionID
                       WHERE p.Estado = 1 AND per.NumeroDocumento = ?";
            $stmtDni = $pdo->prepare($sqlDni);
            $stmtDni->execute([$correo]);
            $medico = $stmtDni->fetch(PDO::FETCH_ASSOC);
        }

        if (!$medico) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró ningún médico o personal activo registrado con ese correo institucional.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $medico['NombreCompleto'] = trim($medico['ApellidoPaterno'] . ' ' . ($medico['ApellidoMaterno'] ?? '') . ', ' . $medico['Nombres']);
        $medico['NombreSimple'] = trim($medico['Nombres'] . ' ' . $medico['ApellidoPaterno']);
        $medico['EspecialidadOcupacion'] = $medico['Especialidad'] ?: ($medico['Cargo'] ?: 'Personal Médico');
        $medico['ModulosAutorizados'] = getModulosPorEspecialidad($medico['EspecialidadOcupacion'], (string)$medico['Cargo']);

        // Guardar en sesion
        $_SESSION['medico_autenticado'] = $medico;

        echo json_encode([
            'success' => true,
            'message' => 'Acceso autorizado correctamente',
            'data'    => $medico
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

/**
 * Determina los modulos de analisis autorizados segun la especialidad y cargo
 */
function getModulosPorEspecialidad(string $especialidad, string $cargo): array {
    $espNorm = mb_strtolower($especialidad, 'UTF-8');
    $cargoNorm = mb_strtolower($cargo, 'UTF-8');

    // Modulo 1: Bioquimica Clinica (Siempre disponible como base o especializado)
    $bioquimica = [
        'id' => 'bioquimica',
        'nombre' => 'Bioquímica Clínica',
        'codigo' => 'EX_BIOQ',
        'subtitulo' => 'Perfil Basal, Lipídico, Hepático, Glucosa y Función Renal',
        'descripcion' => 'Pruebas metabólicas oficiales del Hospital Pacasmayo con valores de referencia por edad y sexo.',
        'icono' => '🧪',
        'disponible' => true,
        'activo' => true,
        'badge' => 'Ficha Oficial A4',
        'url' => 'index.php?modulo=bioquimica'
    ];

    // Modulo 2: Hematologia y Coagulacion
    $hematologia = [
        'id' => 'hematologia',
        'nombre' => 'Hematología y Hemograma',
        'codigo' => 'EX_HEMATO',
        'subtitulo' => 'Hemograma Completo, Fórmula Leucocitaria, Coagulación y Grupo',
        'descripcion' => 'Evaluación de serie roja, serie blanca, recuento plaquetario y constantes corpusculares.',
        'icono' => '🩸',
        'disponible' => true,
        'activo' => (
            strpos($espNorm, 'laboratorio') !== false ||
            strpos($espNorm, 'intensiva') !== false ||
            strpos($espNorm, 'emergencia') !== false ||
            strpos($espNorm, 'pediatr') !== false ||
            strpos($cargoNorm, 'tecn') !== false ||
            strpos($cargoNorm, 'médico') !== false
        ),
        'badge' => 'Disponible',
        'url' => 'index.php?modulo=hematologia'
    ];

    // Modulo 3: Uranalisis y Sedimento
    $uranalisis = [
        'id' => 'uranalisis',
        'nombre' => 'Uranálisis y Sedimento',
        'codigo' => 'EX_ORINA',
        'subtitulo' => 'Examen Físico, Bioquímico de Tira y Sedimento Microscópico',
        'descripcion' => 'Evaluación de orina completa, sedimento urinario, proteinuria y cristaluria.',
        'icono' => '🔬',
        'disponible' => true,
        'activo' => (
            strpos($espNorm, 'laboratorio') !== false ||
            strpos($espNorm, 'pediatr') !== false ||
            strpos($espNorm, 'obstetr') !== false ||
            strpos($espNorm, 'materno') !== false ||
            strpos($cargoNorm, 'médico') !== false
        ),
        'badge' => 'Disponible',
        'url' => 'index.php?modulo=uranalisis'
    ];

    // Modulo 4: Microbiologia y Cultivos
    $microbiologia = [
        'id' => 'microbiologia',
        'nombre' => 'Microbiología y Cultivos',
        'codigo' => 'EX_MICRO',
        'subtitulo' => 'Urocultivos, Coprocultivos, Antibiogramas y Coloraciones Gram',
        'descripcion' => 'Identificación de agentes bacteriológicos y perfil de sensibilidad antibiótica.',
        'icono' => '🧫',
        'disponible' => (
            strpos($espNorm, 'laboratorio') !== false ||
            strpos($espNorm, 'intensiva') !== false ||
            strpos($espNorm, 'emergencia') !== false ||
            strpos($cargoNorm, 'tecn') !== false ||
            strpos($cargoNorm, 'especialista') !== false
        ),
        'activo' => strpos($espNorm, 'laboratorio') !== false,
        'badge' => strpos($espNorm, 'laboratorio') !== false ? 'Especialidad Autorizada' : 'Acceso Restringido',
        'url' => 'index.php?modulo=microbiologia'
    ];

    return [$bioquimica, $hematologia, $uranalisis, $microbiologia];
}
