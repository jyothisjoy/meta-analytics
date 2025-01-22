<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');
session_start();

try {
    $conn = getDBConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get specific user
                $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'user' => $user
                ]);
            } else {
                // Get all users
                $stmt = $conn->query("SELECT id, username, role FROM users");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $users
                ]);
            }
            break;
            
        case 'POST':
            // Get JSON data
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Add new user
            $stmt = $conn->prepare("
                INSERT INTO users (username, password, role) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User added successfully'
            ]);
            break;
            
        case 'PUT':
            // Get JSON data
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!empty($data['password'])) {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET username = ?, password = ?, role = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['username'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['role'],
                    $data['id']
                ]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET username = ?, role = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['username'],
                    $data['role'],
                    $data['id']
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('User ID is required');
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 