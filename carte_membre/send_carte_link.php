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

    // Préparer le message WhatsApp
    $message = [
        'messaging_product' => 'whatsapp',
        'to' => $clean_number,
        'type' => 'text',
        'text' => [
            'body' => "Voici votre carte de membre : " . $membre['carte_url']
        ]
    ];

    // Envoyer le message via l'API WhatsApp
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v21.0/'.WHATSAPP_PHONE_NUMBER_ID.'/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . WHATSAPP_TOKEN,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        throw new Exception('Erreur cURL: ' . $error);
    }

    $response_data = json_decode($response, true);
    if ($http_code == 200) {
        echo json_encode([
            'success' => true,
            'message' => 'Lien de la carte envoyé avec succès',
            'whatsapp_message_id' => $response_data['messages'][0]['id']
        ]);
    } else {
        throw new Exception($response_data['error']['message'] ?? 'Erreur lors de l\'envoi du message WhatsApp');
    }

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