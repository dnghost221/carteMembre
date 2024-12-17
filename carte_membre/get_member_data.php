<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "membre";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer le dernier membre ajouté
$sql = "SELECT * FROM carte_membre ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Convertir la photo en base64 si elle existe
    if ($row['photo']) {
        $row['photo'] = base64_encode($row['photo']);
    }
    
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Aucune donnée trouvée"]);
}

$conn->close();
?>
