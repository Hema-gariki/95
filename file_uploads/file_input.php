<?php
header("Content-Type: application/json");


$host = "localhost";
$username = "root";
$password = "";
$database = "csci6040_study";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    die("Cannot connect DB: " . mysqli_connect_error());
}


// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

// Check file upload
if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => false,
        "message" => "No file uploaded"
    ]);
    exit;
}

$file = $_FILES['file'];

// Validate file type
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'csv') {
    echo json_encode([
        "status" => false,
        "message" => "Only CSV files are allowed"
    ]);
    exit;
}

// Open CSV file
$handle = fopen($file['tmp_name'], "r");

if (!$handle) {
    echo json_encode([
        "status" => false,
        "message" => "Unable to read file"
    ]);
    exit;
}

$data = [];
$rowNumber = 0;

while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

    $rowNumber++;


    if (count($row) < 3) {
        continue;
    }

    if ($rowNumber == 1 && strtolower($row[0]) == 'name') {
        continue;
    }

    $name = trim($row[0]);
    $email = trim($row[1]);
    $password = trim($row[2]);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = mysqli_prepare($con, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
    mysqli_stmt_execute($stmt);

    $data[] = [
        "name" => $name,
        "email" => $email,
        "password" => $hashed_password
    ];
}

fclose($handle);

// Output JSON
echo json_encode([
    "status" => true,
    "message" => "CSV processed successfully",
    "total_records" => count($data),
    "data" => $data
]);

?>