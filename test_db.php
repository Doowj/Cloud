<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
     $_db = new PDO(
        "mysql:host=rds-stack-myrdsinstance-iaphxerfkway.cng5rlrnyzca.us-east-1.rds.amazonaws.com;dbname=ecommerce;charset=utf8mb4",
        "admin",
        "admin123*",
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    echo "✅ Database connected successfully!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}


?>
