<?php
session_start();
// Configuration de l'entête HTTP
// Force le navigateur à interpréter le flux de sortie comme un objet de données json
header('Content-Type: application/json');

// Contrôle d'accès back-office
// Sécurise le endpoint en renvoyant un échec si l'appelant n'est pas authentifié comme administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["success" => false]); 
    exit();
}

// Validation des paramètres de requête
// Vérifie la présence concomitante de l'identifiant de la cible et de l'opération demandée
if (isset($_POST['id_user']) && isset($_POST['action'])) {
    $fichier = 'data/utilisateurs.json';
    $data = json_decode(file_get_contents($fichier), true);
    
    // Recherche et mutation du compte cible
    // Parcourt le registre utilisateur pour identifier la clé correspondante et modifier ses privilèges d'accès
    foreach ($data['utilisateurs'] as $i => $u) {
        if ($u['id'] == $_POST['id_user']) {
            if ($_POST['action'] === 'bloquer') {
                $data['utilisateurs'][$i]['statut'] = 'bloque';
            } elseif ($_POST['action'] === 'debloquer') {
                $data['utilisateurs'][$i]['statut'] = 'actif'; 
            }
            
            // Persistance des modifications
            // Écrit la structure mise à jour dans le fichier de stockage json et valide l'opération auprès du script js
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true]);
            exit();
        }
    }
}

// Traitement des cas de rejet par défaut
// Renvoie un indicateur d'échec si les paramètres requis sont manquants ou si l'ID utilisateur est introuvable
echo json_encode(["success" => false]);
?>
