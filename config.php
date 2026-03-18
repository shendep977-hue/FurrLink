<?php
// FURLINK - PostgreSQL Database Configuration

$host = 'localhost';
$port = '5432';
$dbname = 'furlink';
$user = 'postgres'; // Default postgres user, please adjust in production
$password = 'root';     // Default password, please adjust in production

try {
    // Determine the DSN (Data Source Name) for PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    // Create a PDO instance
    $pdo = new PDO($dsn, $user, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch mode associative
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If there is an error connecting to the database, display it and stop execution
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}
?>
