<?php
function getDb() {
    $host = '127.0.0.1';
    $port = '3306';
    $db   = 'vehicle_app';
    $user = 'root';
    $pass = ''; //local development

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass); //creates db connection
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //in case of fail
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    } catch (PDOException $e) {
        header('Content-Type: application/json');  // ✅ added
        http_response_code(500);
        die(json_encode(["error" => "Internal Server Error. Please try again later."])); //user gets notified
    }
}