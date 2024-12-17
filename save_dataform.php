<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "membre";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Traitement de la photo
    $photo = null;
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) { 
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Préparation de la requête SQL
    $sql = "INSERT INTO carte_membre (prenom, nom, tel, naissance, region, departement, commune, adresse, cni, electeur, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erreur de préparation: " . $conn->error);
    }

    // Après la récupération du numéro de téléphone
    $tel = $_POST['tel'];

    // Nettoyer et formater le numéro
    $tel = preg_replace('/[^0-9]/', '', $tel);

    // Vérifier si le numéro commence par 221 (Sénégal)
    if (!str_starts_with($tel, '221')) {
        $tel = '221' . $tel;
    }

    // Vérifier la longueur du numéro (devrait être 12 chiffres avec le code pays)
    if (strlen($tel) !== 12) {
        echo json_encode(['success' => false, 'error' => 'Format de numéro de téléphone invalide']);
        exit;
    }

    $stmt->bind_param("sssssssssss", 
        $_POST['prenom'],
        $_POST['nom'],
        $tel,
        $_POST['naissance'],
        $_POST['region'],
        $_POST['departement'],
        $_POST['commune'],
        $_POST['adresse'],
        $_POST['cni'],
        $_POST['electeur'],
        $photo
    );

    if ($stmt->execute()) {
        $memberId = $conn->insert_id;
        echo json_encode(['success' => true, 'memberId' => $memberId]);
    } else {
        throw new Exception("Erreur d'exécution: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>