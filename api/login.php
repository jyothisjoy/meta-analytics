<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    // Get JSON data from POST request
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['username']) || empty($data['password'])) {
        throw new Exception('Username and password are required');
    }

    $conn = getDBConnection();

    // Prepare the select statement
    $stmt = $conn->prepare("
        SELECT id, username, password 
        FROM users 
        WHERE username = ?
    ");

    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user exists and password is correct
    if ($user && password_verify($data['password'], $user['password'])) {
        // Start session and set user data
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful'
        ]);
    } else {
        throw new Exception('Invalid username or password');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 