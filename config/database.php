<?php
/* =========================================================
   CONFIGURACION CENTRALIZADA DE BASE DE DATOS
   Hospital Distrital Pacasmayo - Modulo de Laboratorio
   Soporta: PDO_SQLSRV y PDO_ODBC (ODBC Driver 17/18 for SQL Server)
   ========================================================= */

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    // Parametros de conexion configurables
    private static string $host = '.\\SQLEXPRESS';
    private static string $dbname = 'BD_HOSPITAL';
    private static ?string $username = null; // null para Autenticacion Integrada de Windows
    private static ?string $password = null;

    /**
     * Obtiene la instancia unica de PDO conectada a SQL Server
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $drivers = PDO::getAvailableDrivers();

                if (in_array('sqlsrv', $drivers, true)) {
                    // Cadena DSN para PDO_SQLSRV
                    $dsn = "sqlsrv:Server=" . self::$host . ";Database=" . self::$dbname . ";TrustServerCertificate=true";
                    $options = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
                    ];

                    if (self::$username !== null && self::$username !== '') {
                        self::$instance = new PDO($dsn, self::$username, self::$password ?? '', $options);
                    } else {
                        self::$instance = new PDO($dsn, null, null, $options);
                    }
                } elseif (in_array('odbc', $drivers, true)) {
                    // Conexion mediante PDO_ODBC con controlador ODBC 17 o 18
                    $driverName = 'ODBC Driver 17 for SQL Server';
                    $dsn = "odbc:Driver={" . $driverName . "};Server=" . self::$host . ";Database=" . self::$dbname . ";";

                    if (self::$username !== null && self::$username !== '') {
                        $dsn .= "Uid=" . self::$username . ";Pwd=" . (self::$password ?? '') . ";";
                        $user = self::$username;
                        $pass = self::$password ?? '';
                    } else {
                        $dsn .= "Trusted_Connection=yes;";
                        $user = null;
                        $pass = null;
                    }

                    $options = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ];

                    self::$instance = new PDO($dsn, $user, $pass, $options);
                } else {
                    throw new RuntimeException("No se encontro ningun driver compatible con SQL Server (sqlsrv u odbc).");
                }
            } catch (Throwable $e) {
                if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/api/')) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error'   => 'Error de conexion a SQL Server: ' . $e->getMessage()
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                die("Error al conectar con la Base de Datos Institucional (BD_HOSPITAL): " . htmlspecialchars($e->getMessage()));
            }
        }

        return self::$instance;
    }

    /**
     * Permite sobreescribir los parametros de conexion
     */
    public static function configure(string $host, string $dbname, ?string $user = null, ?string $pass = null): void
    {
        self::$host = $host;
        self::$dbname = $dbname;
        self::$username = $user;
        self::$password = $pass;
        self::$instance = null;
    }
}
