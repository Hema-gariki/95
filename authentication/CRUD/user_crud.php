<?php
require_once "db_connection.php";
header("Content-Type: application/json");

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

$bodyData = [];
parse_str(file_get_contents("php://input"), $bodyData);

try {

    // ================= CREATE (POST) =================
    if ($method === "POST") {

        $name  = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");

        if ($name === "" || $email === "") {
            respond(400, ["error" => "name and email are required"]);
        }

        // Default password (stored only in DB, not returned)
        $default_password = "Default@123";
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

        // Check duplicate email
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            respond(409, ["error" => "Email already exists"]);
        }

        // Insert user
        $stmt = $con->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        $stmt->execute();

        respond(201, [
            "message" => "User created",
            "id" => $stmt->insert_id,
            "name" => $name,
            "email" => $email
        ]);
    }

    // ================= READ (GET) =================
    if ($method === "GET") {

        $user_id = intval($_GET["user_id"] ?? 0);

        // READ ONE
        if ($user_id > 0) {

            $stmt = $con->prepare("SELECT id, name, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                respond(404, ["error" => "User not found"]);
            }

            respond(200, $user);
        }

        // LIST ALL
        $result = $con->query("SELECT id, name, email FROM users ORDER BY id DESC");

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        respond(200, ["users" => $users]);
    }

    // ================= UPDATE (PUT) =================
    if ($method === "PUT") {

        $user_id = intval($bodyData["user_id"] ?? 0);
        $name     = trim($bodyData["name"] ?? "");
        $email    = trim($bodyData["email"] ?? "");
        $password = trim($bodyData["password"] ?? "");

        if (!$user_id) {
            respond(400, ["error" => "Valid user_id is required"]);
        }

        if ($name === "" && $email === "" && $password === "") {
            respond(400, ["error" => "Provide name, email or password"]);
        }

        if ($name !== "") {
            $stmt = $con->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $user_id);
            $stmt->execute();
        }

        if ($email !== "") {
            $stmt = $con->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
        }

        if ($password !== "") {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
        }

        respond(200, ["message" => "User updated"]);
    }

    // ================= DELETE (DELETE) =================
    if ($method === "DELETE") {

        $user_id = intval($bodyData["user_id"] ?? 0);

        if (!$user_id) {
            respond(400, ["error" => "Valid user_id is required"]);
        }

        $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            respond(404, ["error" => "User not found"]);
        }

        respond(200, ["message" => "User deleted"]);
    }

    respond(405, ["error" => "Method not allowed"]);

} catch (Exception $e) {
    respond(500, ["error" => "Server error"]);
}
?>