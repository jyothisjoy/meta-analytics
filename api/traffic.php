<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Enable CORS if needed
header('Content-Type: application/json');

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get JSON data from POST request
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['date']) || empty($data['data'])) {
        throw new Exception('Missing required fields');
    }

    $conn = getDBConnection();
    $conn->beginTransaction();

    // First, get or create hotel IDs
    $hotelStmt = $conn->prepare("
        INSERT IGNORE INTO hotels (hotel_name) 
        VALUES (?)
    ");

    $getHotelIdStmt = $conn->prepare("
        SELECT id FROM hotels WHERE hotel_name = ?
    ");

    // Prepare the traffic data insert statement
    $trafficStmt = $conn->prepare("
        INSERT INTO traffic_data 
        (date, hotel_id, expected_traffic, new_users, bookings, created_by) 
        VALUES 
        (?, ?, ?, ?, ?, ?)
    ");

    // Insert each row
    foreach ($data['data'] as $row) {
        // First ensure hotel exists and get its ID
        $hotelStmt->execute([$row['hotel_name']]);
        $getHotelIdStmt->execute([$row['hotel_name']]);
        $hotelResult = $getHotelIdStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$hotelResult) {
            throw new Exception("Failed to get hotel ID for " . $row['hotel_name']);
        }

        $trafficStmt->execute([
            $data['date'],
            $hotelResult['id'],
            $row['expected_traffic'],
            $row['new_users'],
            $row['bookings'],
            $_SESSION['user_id']
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Data saved successfully']);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 