<?php
require_once 'config.php';
require_once 'whatsapp_config.php';
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "membre";

try {
    // Connexion à la base de données
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Récupérer l'ID du membre depuis la requête
    $memberId = $_GET['id'] ?? null;
    if (!$memberId) {
        throw new Exception("ID du membre non fourni");
    }

    // Récupérer les informations du membre
    $sql = "SELECT tel, carte_url FROM carte_membre WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Membre non trouvé");
    }

    $membre = $result->fetch_assoc();
    
    // Nettoyer le numéro de téléphone
    $clean_number = preg_replace('/[^0-9]/', '', $membre['tel']);
    if (!str_starts_with($clean_number, '221')) {
        $clean_number = '221' . $clean_number;
    }

    // Créer le lien WhatsApp direct
    $message = urlencode("Voici votre carte de membre : " . $membre['carte_url']);
    $whatsapp_link = "https://wa.me/" . $clean_number . "?text=" . $message;

    // Retourner le lien
    echo json_encode([
        'success' => true,
        'whatsapp_link' => $whatsapp_link
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 