<?php
require 'database.php';
require 'CarDataValidation.php';
require 'VehicleModel.php';
require 'VehicleController.php';

// Create database connection
$db = getDb();
$model = new VehicleModel($db); // Create model instance
$controller = new VehicleController($model);

$method = $_SERVER['REQUEST_METHOD']; // Get the HTTP request method (GET, POST, PUT, DELETE)
$input = json_decode(file_get_contents('php://input'), true) ?? [];


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($uri, '/'));
$id = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
// Route request based on HTTP method
switch ($method) {
    case 'GET':
        $controller->getAll($_GET);
        break;

    case 'POST':
        //create new vehicle using json data
        $controller->create($input);
        break;

    case 'PUT':
        if (!$id) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]); //if no ID, returns error
            break;
        }
        $controller->update($id, $input); //update call with ID and input data
        break;

    case 'DELETE':
        if (!$id) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(["error" => "ID is required e.g. /vehicles/5"]); //if no ID, error
            break;
        }
        $controller->delete($id);
        break;

    default:
        header('Content-Type: application/json');
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]); //error if HTTP method is not supported
        break;
}