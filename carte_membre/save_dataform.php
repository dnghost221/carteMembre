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

    // Démarrer la transaction
    $conn->begin_transaction();

    // Vérifier si le numéro de téléphone existe déjà
    $tel = $_POST['tel'];
    $check_stmt = $conn->prepare("SELECT tel FROM carte_membre WHERE tel = ?");
    $check_stmt->bind_param("s", $tel);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception("Ce numéro de téléphone existe déjà");
    }
    $check_stmt->close();

    // Vérifier si la CNI existe déjà
    $cni = $_POST['cni'];
    $check_stmt = $conn->prepare("SELECT cni FROM carte_membre WHERE cni = ?");
    $check_stmt->bind_param("s", $cni);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception("Ce numéro de CNI existe déjà");
    }
    $check_stmt->close();

    // Vérifier si la carte d'électeur existe déjà
    $electeur = $_POST['electeur'];
    $check_stmt = $conn->prepare("SELECT electeur FROM carte_membre WHERE electeur = ?");
    $check_stmt->bind_param("s", $electeur);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception("Ce numéro de carte d'électeur existe déjà");
    }
    $check_stmt->close();

    // Traitement de la photo
    $photo = null;
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) { 
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Préparation et exécution de l'insertion
    $sql = "INSERT INTO carte_membre (prenom, nom, tel, naissance, region, departement, commune, adresse, cni, electeur, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erreur de préparation: " . $conn->error);
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
        $cni,
        $electeur,
        $photo
    );

    // Exécuter l'insertion
    if (!$stmt->execute()) {
        throw new Exception("Erreur lors de l'insertion: " . $stmt->error);
    }

    // Récupérer l'ID inséré
    $lastId = $conn->insert_id;

    // Tester la génération de la carte
    if (!generate_carte($lastId)) {
        throw new Exception("Échec de la génération de la carte");
    }

    // Si tout est OK, on valide la transaction
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "memberId" => $lastId
    ]);

} catch (Exception $e) {
    // En cas d'erreur, on annule toute la transaction
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

function generate_carte($id) {
    // Ajoutez ici votre logique de génération de carte
    return true;
}
?>