<?php
function getDB() {
    try {
        $host = 'localhost';
        $dbname = 'meta';
        $username = 'root';
        $password = '';
        
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch(PDOException $e) {
        error_log("Connection Error: " . $e->getMessage());
        throw new Exception("Connection failed");
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function formatDate($date) {
    return date('Y-m-d', strtotime($date));
} 