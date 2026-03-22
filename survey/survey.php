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


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

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


    $data[] = [
    "Question" => trim($row[0]),
    "CorrectAnswer" => trim($row[1]),
    "Wrong1" => trim($row[2]),
    "Wrong2" => trim($row[3]),
    "Wrong3" => trim($row[4])
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