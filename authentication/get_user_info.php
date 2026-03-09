<?php
require_once "db_connection.php";
// Check if user_id is provided
if (!isset($_POST['user_id'])) {
http_response_code(400); // Bad Request
echo json_encode(['error' => 'user_id is required']);
exit;
}
// Sanitize and validate the user_id parameter
$user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
if (!$user_id) {
http_response_code(400); // Bad Request
echo json_encode(['error' => 'Invalid user_id']);
exit;
}
// Query the database for user information
try {
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if ($user) {
// Return the user information as JSON
http_response_code(200); // OK
echo json_encode($user);
} else {
// User not found
http_response_code(404); // Not Found
echo json_encode(['error' => 'User not found']);
}
} catch (PDOException $e) {
http_response_code(500); // Internal Server Error
echo json_encode(['error' => 'Query failed']);
}
?>