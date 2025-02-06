<?php
// Database connection settings
$host = 'localhost';  // or your DB host
$dbname = 'finance_tracker';  // Database name
$username = 'root';  // Database username (default for XAMPP)
$password = '';  // Database password (default for XAMPP)

try {
    // Set DSN (Data Source Name)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // PDO options for better error handling
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    // Create PDO instance (database connection)
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}
?>
