<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => '', 'data' => null, 'errors' => []];

try {
    switch ($method) {
        
        case 'GET':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :id");
                $stmt->execute([':id' => $id]);
                $event = $stmt->fetch();

                if ($event) {
                    $response['success'] = true;
                    $response['data'] = $event;
                    http_response_code(200);
                } else {
                    $response['message'] = "Event not found.";
                    http_response_code(404);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
                $events = $stmt->fetchAll();
                $response['success'] = true;
                $response['data'] = $events;
                http_response_code(200);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents("php://input"), true);
        
            $title       = trim($input['title'] ?? '');
            $category    = trim($input['category'] ?? '');
            $eventDate   = trim($input['event_date'] ?? '');
            $location    = trim($input['location'] ?? '');
            $maxCapacity = intval($input['max_capacity'] ?? 0);

            if (empty($title) || empty($category) || empty($eventDate) || empty($location) || $maxCapacity <= 0) {
                $response['message'] = "Validation failed. Please fill all required fields correctly.";
                http_response_code(400);
                break;
            }

            $sql = "INSERT INTO events (title, category, event_date, location, max_capacity, status) 
                    VALUES (:title, :category, :event_date, :location, :capacity, 'UPCOMING')";
            $stmt = $pdo->prepare($sql);
            $exec = $stmt->execute([
                ':title'    => $title,
                ':category' => $category,
                ':event_date' => $eventDate,
                ':location' => $location,
                ':capacity' => $maxCapacity
            ]);

            if ($exec) {
                $response['success'] = true;
                $response['message'] = "Event created successfully!";
                $response['data'] = ['event_id' => $pdo->lastInsertId()];
                http_response_code(201);
            } else {
                $response['message'] = "Failed to insert record into database.";
                http_response_code(500);
            }
            break;
        case 'PUT':
            $input = json_decode(file_get_contents("php://input"), true);

            $id          = intval($input['event_id'] ?? 0);
            $title       = trim($input['title'] ?? '');
            $category    = trim($input['category'] ?? '');
            $eventDate   = trim($input['event_date'] ?? '');
            $location    = trim($input['location'] ?? '');
            $maxCapacity = intval($input['max_capacity'] ?? 0);
            $status      = trim($input['status'] ?? 'UPCOMING');

            if ($id <= 0 || empty($title) || empty($category) || empty($eventDate)) {
                $response['message'] = "Invalid payload parameters provided.";
                http_response_code(400);
                break;
            }

            $sql = "UPDATE events SET title = :title, category = :category, event_date = :event_date, 
                    location = :location, max_capacity = :capacity, status = :status WHERE event_id = :id";
            $stmt = $pdo->prepare($sql);
            $exec = $stmt->execute([
                ':title'    => $title,
                ':category' => $category,
                ':event_date' => $eventDate,
                ':location' => $location,
                ':capacity' => $maxCapacity,
                ':status'   => $status,
                ':id'       => $id
            ]);

            if ($exec) {
                $response['success'] = true;
                $response['message'] = "Event record updated successfully!";
                http_response_code(200);
            } else {
                $response['message'] = "Failed to update record.";
                http_response_code(500);
            }
            break;
        case 'DELETE':
            $input = json_decode(file_get_contents("php://input"), true);
            $id = intval($input['event_id'] ?? $_GET['id'] ?? 0);

            if ($id <= 0) {
                $response['message'] = "Invalid or missing Event ID for deletion.";
                http_response_code(400);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :id");
            if ($stmt->execute([':id' => $id])) {
                $response['success'] = true;
                $response['message'] = "Event deleted successfully!";
                http_response_code(200);
            } else {
                $response['message'] = "Database deletion operation failed.";
                http_response_code(500);
            }
            break;

        default:
            $response['message'] = "HTTP Method Not Allowed.";
            http_response_code(405);
            break;
    }

} catch (PDOException $e) {
    error_log("API Error: " . $e->getMessage());
    $response['message'] = "Internal Server Error.";
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
