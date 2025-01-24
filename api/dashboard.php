<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $conn = getDBConnection();
    
    $type = $_GET['type'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $hotelId = $_GET['hotel_id'] ?? '';
    
    if (!$type || !$startDate || !$endDate || !$hotelId) {
        throw new Exception('Missing required parameters');
    }

    if ($type === 'traffic') {
        $query = "SELECT date, expected_traffic, new_users, bookings 
                 FROM traffic_data 
                 WHERE hotel_id = :hotel_id 
                 AND date BETWEEN :start_date AND :end_date 
                 ORDER BY date";
    } else {
        $query = "SELECT date, number_of_rooms, booking_target, actual_bookings, booked_nights 
                 FROM booking_data 
                 WHERE hotel_id = :hotel_id 
                 AND date BETWEEN :start_date AND :end_date 
                 ORDER BY date";
    }

    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':hotel_id' => $hotelId,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        $type => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 