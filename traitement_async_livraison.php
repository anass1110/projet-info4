<?php
session_start();
// Configuration de l'entête HTTP
// Indique explicitement au client que le flux de retour retourné par le serveur est un document json
header('Content-Type: application/json');

// Contrôle d'accès back-office
// Sécurise le point d'accès en retournant un échec immédiat si l'appelant ne possède pas le rôle livreur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    echo json_encode(["success" => false]); 
    exit();
}

// Validation des données d'entrée
// Vérifie la réception obligatoire de la référence de la commande et du jeton d'action associé
if (isset($_POST['id_commande']) && isset($_POST['action'])) {
    $fichier = 'data/commandes.json';
    $data = json_decode(file_get_contents($fichier), true);
    
    // Traitement logistique de la course
    // Parcourt le registre pour identifier l'enregistrement correspondant et modifier le statut de livraison
    foreach ($data['commandes'] as $i => $c) {
        if ($c['id_commande'] == $_POST['id_commande']) {
            if ($_POST['action'] === 'valider') {
                $data['commandes'][$i]['statut'] = 'Livrée';
            } elseif ($_POST['action'] === 'abandonner') {
                $data['commandes'][$i]['statut'] = 'Erreur Livraison';
            }
            
            // Persistance et acquittement de la transaction
            // Réécrit la structure modifiée dans le fichier json et renvoie un accusé de réussite au client js
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true]);
            exit();
        }
    }
}

// Interception des appels incorrects
// Renvoie un objet json d'échec par défaut si l'identifiant est introuvable ou les paramètres invalides
echo json_encode(["success" => false]);
?>
