<?php
function getLiensCarte() {
    $log_file = 'cartes/cartes_links.txt';
    if (file_exists($log_file)) {
        return file_get_contents($log_file);
    }
    return "Aucun lien enregistré";
}

// Pour utiliser en mode API
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    $liens = array_filter(explode("\n", getLiensCarte()));
    echo json_encode($liens);
} else {
    header('Content-Type: text/plain');
    echo getLiensCarte();
} 