<?php
// Configuration de l'environnement
define('ENV', 'development'); // Changer en 'production' une fois en ligne

// Configuration de l'URL de base
if (ENV === 'development') {
    define('BASE_URL', 'http://localhost/carteMembre/');
} else {
    define('BASE_URL', 'https://localhost/carteMembre/'); // À remplacer par votre vrai domaine
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'membre');

// Dossier de stockage des cartes
define('CARDS_DIR', 'cartes/'); 