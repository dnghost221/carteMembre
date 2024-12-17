<?php
require_once 'config.php';
require_once 'whatsapp_config.php';
header('Content-Type: application/json');

// Vérifier si le dossier cartes existe, sinon le créer
if (!file_exists(CARDS_DIR)) {
    mkdir(CARDS_DIR, 0777, true);
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['imageData']) && isset($data['memberId'])) {
    // Extraire les données de l'image
    $imageData = $data['imageData'];
    $memberId = $data['memberId'];
    
    // Nettoyer les données de l'image
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    
    // Décoder les données base64 
    $imageData = base64_decode($imageData);
    
    // Créer le nom du fichier
    $fileName = 'cartes/carte_membre_' . $memberId . '_' . date('Y-m-d_His') . '.png';
    
    // Sauvegarder l'image
    if (file_put_contents($fileName, $imageData)) {
        // Connexion à la base de données pour récupérer le numéro de téléphone
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "membre";

        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'error' => 'Erreur de connexion DB']);
            exit;
        }

        // Récupérer le numéro de téléphone
        $sql = "SELECT tel FROM carte_membre WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $phone_number = $row['tel'];
            
            // Créer l'URL complète de la carte
            $carte_url = BASE_URL . $fileName;
            
            // Nettoyer le numéro de téléphone
            $clean_number = preg_replace('/[^0-9]/', '', $phone_number);
            
            // S'assurer que le numéro est au format international
            if (!str_starts_with($clean_number, '221')) {
                $clean_number = '221' . $clean_number;
            }

            // Préparer le message pour WhatsApp avec template
            $message = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $clean_number,
                'type' => 'template',
                'template' => [
                    'name' => 'carte_membre',
                    'language' => [
                        'code' => 'fr'
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $carte_url
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Envoyer le message via l'API WhatsApp Business
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
                echo json_encode([
                    'success' => true,
                    'file' => $fileName,
                    'whatsapp_status' => 'error',
                    'whatsapp_error' => 'Erreur cURL: ' . $error,
                    'message' => 'Carte générée mais erreur lors de l\'envoi WhatsApp'
                ]);
            } else {
                $response_data = json_decode($response, true);
                if ($http_code == 200) {
                    echo json_encode([
                        'success' => true,
                        'file' => $fileName,
                        'whatsapp_status' => 'success',
                        'whatsapp_message_id' => $response_data['messages'][0]['id'],
                        'message' => 'Carte générée et message WhatsApp envoyé avec succès'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'file' => $fileName,
                        'whatsapp_status' => 'error',
                        'whatsapp_error' => $response_data['error']['message'] ?? $response,
                        'message' => 'Carte générée mais erreur lors de l\'envoi WhatsApp'
                    ]);
                }
            }

            $conn->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Numéro de téléphone non trouvé']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
}
