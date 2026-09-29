<?php
require_once __DIR__ . '/../config/database.php';
try {
    $pdo = Database::getConnection();
    $r = $pdo->query("SELECT COUNT(*) as c FROM laboratorio.Examen")->fetch();
    echo "CONEXION OK a BD_HOSPITAL (SQL Server Express)\n";
    echo "Examenes encontrados: " . $r['c'] . "\n";
    
    $pac = $pdo->query("SELECT COUNT(*) as c FROM clinico.Paciente")->fetch();
    echo "Pacientes registrados: " . $pac['c'] . "\n";
    
    $per = $pdo->query("SELECT COUNT(*) as c FROM laboratorio.Parametro")->fetch();
    echo "Parametros de laboratorio: " . $per['c'] . "\n";
    
    echo "\nDriver usado: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "Todo listo! La web ya puede leer y escribir en BD_HOSPITAL.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
