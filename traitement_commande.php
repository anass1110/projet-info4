<?php
session_start();

// Protection : Seul le restaurateur peut modifier l'état d'une commande
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header("Location: accueil.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $fichier = 'data/commandes.json';
    
    if (file_exists($fichier)) {
        $data = json_decode(file_get_contents($fichier), true);
        
        if ($_POST['action'] === 'attribuer_livreur') {
            $id_cmd = $_POST['id_commande'];
            $id_livreur = $_POST['id_livreur'];
            
            // Recherche de la commande et mise à jour
            foreach ($data['commandes'] as $index => $c) {
                if ($c['id_commande'] === $id_cmd) {
                    $data['commandes'][$index]['id_livreur'] = $id_livreur;
                    // On bascule automatiquement le statut pour que le livreur la voie
                    $data['commandes'][$index]['statut'] = 'En livraison'; 
                    break;
                }
            }
            
            // Sauvegarde de la modification
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
        }
    }
}

// Redirection vers le tableau de bord
header("Location: Commandes.php");
exit();
?>
