<?php
// Datenbank-Konfiguration
// Passe diese Werte an deine MySQL-Datenbank an

define('DB_HOST', 'sqlde01.enjyn.de:3306');         // MySQL Server Adresse
define('DB_USER', 'user_13924934');                // MySQL Benutzername
define('DB_PASS', 'bd6398ab6144adb8');             // MySQL Passwort
define('DB_NAME', 'db_101e36c6');                  // Datenbankname

// Datenbankverbindung herstellen
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()]);
        exit;
    }
}

// CORS-Header für Cross-Origin-Requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?>
