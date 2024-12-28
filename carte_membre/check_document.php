<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "membre";    

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$type = $_GET['type'] ?? '';
$value = $_GET['value'] ?? '';

if ($type === 'cni' || $type === 'electeur') {
    $stmt = $conn->prepare("SELECT $type FROM carte_membre WHERE $type = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode(["exists" => $result->num_rows > 0]);

    $stmt->close();
} else {
    echo json_encode(["error" => "Type de document invalide"]);
}

$conn->close();
?> 