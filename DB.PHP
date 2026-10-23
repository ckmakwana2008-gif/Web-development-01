<?php
/* ==========================================================================
   StudentHub PDO Centralized Database Connection Configuration
   Course Code: ITUE203 - Practical 08
   ========================================================================== */

$host     = '127.0.0.1';
$db       = 'studenthub_db';
$user     = 'root';
$pass     = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw PDOException on failure
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Native prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log error securely and show user-friendly response
    error_log("Database Connection Failure: " . $e->getMessage());
    die('<div style="color:red; font-family:sans-serif; padding:1rem;">
            <strong>Database Connection Error:</strong> Unable to connect to backend server.
         </div>');
}
?>
