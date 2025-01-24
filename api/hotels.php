<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if required files exist
if (!file_exists('../includes/db.php')) {
    die(json_encode([
        'success' => false,
        'message' => 'db.php file not found'
    ]));
}

if (!file_exists('../includes/functions.php')) {
    die(json_encode([
        'success' => false,
        'message' => 'functions.php file not found'
    ]));
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Test database connection
    $db = getDB();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // First, check if the hotels table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'hotels'");
    if ($checkTable->rowCount() == 0) {
        throw new Exception("Hotels table does not exist");
    }
    
    // Get hotels list
    $query = "SELECT id, hotel_name FROM hotels ORDER BY hotel_name ASC";
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . print_r($db->errorInfo(), true));
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . print_r($stmt->errorInfo(), true));
    }
    
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($hotels === false) {
        throw new Exception("Failed to fetch hotels data");
    }
    
    echo json_encode([
        'success' => true,
        'hotels' => $hotels
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 