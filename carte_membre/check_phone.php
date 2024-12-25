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

$tel = $_GET['tel'] ?? '';

$stmt = $conn->prepare("SELECT tel FROM carte_membre WHERE tel = ?");
$stmt->bind_param("s", $tel);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode(["exists" => $result->num_rows > 0]);

$stmt->close();
$conn->close();
?> 