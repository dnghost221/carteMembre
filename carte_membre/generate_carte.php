<?php
header('Content-Type: text/html');  // Changé pour permettre l'affichage HTML

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "membre";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("ID non spécifié");
}

$id = $_GET['id'];

// Récupérer les données du membre
$sql = "SELECT * FROM carte_membre WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $membre = $result->fetch_assoc();
    $photoBase64 = '';
    if ($membre['photo']) {
        $photoBase64 = base64_encode($membre['photo']);
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Génération de la carte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="carte-container">
        <div class="carterecto">
            <div class="details">
                <div>Prénoms : <span id="prenom-display"><?php echo htmlspecialchars($membre['prenom']); ?></span></div>
                <div>Nom : <span id="nom-display"><?php echo htmlspecialchars($membre['nom']); ?></span></div>
                <div>N° de tél : <span id="tel-display"><?php echo htmlspecialchars($membre['tel']); ?></span></div>
                <div>Date et lieu de naissance : <span id="naissance-display"><?php echo htmlspecialchars($membre['naissance']); ?></span></div>
                <div>Région : <span id="region-display"><?php echo htmlspecialchars($membre['region']); ?></span></div>
                <div>Département : <span id="departement-display"><?php echo htmlspecialchars($membre['departement']); ?></span></div>
                <div>Commune : <span id="commune-display"><?php echo htmlspecialchars($membre['commune']); ?></span></div>
                <div>Adresse : <span id="adresse-display"><?php echo htmlspecialchars($membre['adresse']); ?></span></div>
                <div>N° Carte CNI : <span id="cni-display"><?php echo htmlspecialchars($membre['cni']); ?></span></div>
                <div>N° Carte d'Électeur : <span id="electeur-display"><?php echo htmlspecialchars($membre['electeur']); ?></span></div>
            </div>

            <div class="signature">
                <div class="phototit">
                    <div class="photo" id="photo-display" style="background-image: url(data:image/jpeg;base64,<?php echo $photoBase64; ?>); background-size: cover;"></div>
                    <div class="titulaire"><u>Le titulaire</u></div>
                </div>
                
                <div class="membrebureau">
                    <div class="role">
                        <div class="president"><u>Le Président</u></div>
                    </div>
                    <div class="role">
                        <div class="tresorier"><u>Le Trésorier</u></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="carteverso">
            <div class="region1"><span id="region-display1"><?php echo htmlspecialchars($membre['region']); ?></span></div>
            <div class="membre"><span id="numero-membre"><?php echo htmlspecialchars($membre['id']); ?></span></div>
        </div>
    </div>

    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        window.onload = function() {
            html2canvas(document.querySelector(".carte-container")).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                
                fetch('save_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        imageData: imgData,
                        memberId: <?php echo json_encode($membre['id']); ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Réponse du serveur:', data);
                    if (data.success) {
                        // Faire une requête pour obtenir le lien WhatsApp
                        fetch(`send_carte_link.php?id=<?php echo $membre['id']; ?>`)
                            .then(response => response.json())
                            .then(whatsappData => {
                                if (whatsappData.success) {
                                    window.location.href = whatsappData.whatsapp_link;
                                } else {
                                    alert('Erreur lors de la génération du lien WhatsApp: ' + whatsappData.error);
                                }
                            })
                            .catch(error => {
                                console.error('Erreur WhatsApp:', error);
                                alert('Erreur lors de la génération du lien WhatsApp');
                            });
                    } else {
                        alert('Erreur lors de la sauvegarde de la carte : ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Erreur détaillée:', error);
                    alert('Erreur: ' + error.message);
                });
            });
        };
    </script>
</body>
</html>
<?php
} else {
    echo "Membre non trouvé";
}

$conn->close();
?>