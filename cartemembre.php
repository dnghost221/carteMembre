<?php
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
    $membre = $result->fetch_assoc();
    $photoBase64 = '';  
    if ($membre['photo']) {
        $photoBase64 = base64_encode($membre['photo']);
    }
} else {
    die("Aucune donnée trouvée");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte Membre</title>
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
    
    <div class="buttonTel">
        <button onclick="generateImage()">Télécharger la carte</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function generateImage() {
            const carteContainer = document.querySelector(".carte-container");
            
            html2canvas(carteContainer).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                
                // Envoyer l'image au serveur
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
                    if (data.success) {
                        alert('Carte enregistrée avec succès !');
                        // Téléchargement optionnel
                        const link = document.createElement("a");
                        link.href = imgData;
                        link.download = "carte_membre.png";
                        link.click();
                    } else {
                        alert('Erreur lors de l\'enregistrement de la carte');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de l\'enregistrement de la carte');
                });
            });
        }
    </script>
</body>
</html>
