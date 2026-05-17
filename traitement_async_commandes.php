<?php
session_start();
// Configuration de l'entête HTTP
// Spécifie au client que la réponse renvoyée par le serveur est un flux de données au format json
header('Content-Type: application/json');

// Contrôle d'accès back-office
// Interdit l'accès au endpoint si l'appelant n'est pas authentifié avec les privilèges de restaurateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    echo json_encode(["success" => false]); 
    exit();
}

// Validation des données d'entrée
// Vérifie la réception conjointe de la clé de la commande et de l'instruction de changement d'état
if (isset($_POST['id_commande']) && isset($_POST['action'])) {
    $fichier = 'data/commandes.json';
    $data = json_decode(file_get_contents($fichier), true);
    
    // Traitement du flux d'état de la commande
    // Parcourt l'historique pour trouver l'enregistrement cible et mettre à jour sa phase de traitement
    foreach ($data['commandes'] as $i => $c) {
        if ($c['id_commande'] == $_POST['id_commande']) {
            if ($_POST['action'] === 'demarrer') {
                $data['commandes'][$i]['statut'] = 'En cours';
                $nouveau_statut = 'En cours';
            } elseif ($_POST['action'] === 'prete') {
                $data['commandes'][$i]['statut'] = 'En attente';
                $nouveau_statut = 'En attente';
            }
            
            // Écriture et confirmation de la transaction
            // Sauvegarde le nouvel état dans le fichier json et retourne le statut mis à jour au script js
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true, "nouveau_statut" => $nouveau_statut]);
            exit();
        }
    }
}

// Interception des anomalies de requête
// Renvoie un message d'échec standardisé si les paramètres POST requis sont absents ou erronés
echo json_encode(["success" => false]);
?>
