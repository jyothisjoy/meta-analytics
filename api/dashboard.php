<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get date range from request
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    // Fetch traffic data
    $trafficQuery = "
        SELECT date, hotel_name, expected_traffic, new_users, bookings 
        FROM traffic_data 
        WHERE date BETWEEN ? AND ?
        ORDER BY date, hotel_name
    ";
    
    // Fetch booking data
    $bookingQuery = "
        SELECT date, hotel_name, number_of_rooms, booking_target, actual_bookings, booked_nights 
        FROM booking_data 
        WHERE date BETWEEN ? AND ?
        ORDER BY date, hotel_name
    ";
    
    // Get traffic data
    $stmt = $conn->prepare($trafficQuery);
    $stmt->execute([$startDate, $endDate]);
    $trafficData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get booking data
    $stmt = $conn->prepare($bookingQuery);
    $stmt->execute([$startDate, $endDate]);
    $bookingData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'traffic' => $trafficData,
        'bookings' => $bookingData
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 