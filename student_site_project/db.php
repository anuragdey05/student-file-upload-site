<?php
// db.php
require_once 'config.php';

function get_db_connection() {
    static $pdo = null; // Static variable to hold the connection
    if ($pdo === null) {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // In a real app, log this error and show a generic message
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
?>
