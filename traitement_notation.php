<?php
session_start();

// Protection du script de traitement
// Interdit l'exécution directe si la requête n'est pas un POST ou si l'utilisateur n'est pas connecté
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
    
    // Enregistrement de l'avis dans le registre des commentaires
    $fichier_avis = 'data/avis.json';
    
    // Création de l'architecture JSON racine si nécessaire
    if(!file_exists($fichier_avis)) {
        file_put_contents($fichier_avis, json_encode(["avis" => []]));
    }
    
    $data_avis = json_decode(file_get_contents($fichier_avis), true); 
    
    // Nettoyage et typage strict des données entrantes 
    $nouvel_avis = [
        "id_commande" => $_POST['id_commande'] ?? 'N/D', // Trame de liaison avec la commande
        "id_client" => $_SESSION['user']['id'],
        "note_produit" => intval($_POST['note_produit']),
        "note_livraison" => intval($_POST['note_livraison']),
        "commentaire" => trim($_POST['user_commentaire']), 
        "date" => date('Y-m-d H:i:s')
    ];
    
    $data_avis['avis'][] = $nouvel_avis;
    file_put_contents($fichier_avis, json_encode($data_avis, JSON_PRETTY_PRINT)); 
    
    // Verrouillage de la commande pour empêcher la double notation
    // Récupère l'ID envoyé en tâche de fond pour marquer la commande comme déjà notée
    if (isset($_POST['id_commande'])) {
        $id_cmd_cible = $_POST['id_commande'];
        $fichier_commandes = 'data/commandes.json';
        
        if (file_exists($fichier_commandes)) {
            $data_cmd = json_decode(file_get_contents($fichier_commandes), true);
            
            // Recherche de la commande dans le fichier relationnel JSON
            foreach ($data_cmd['commandes'] as $i => $commande) {
                if ($commande['id_commande'] == $id_cmd_cible) {
                    $data_cmd['commandes'][$i]['deja_note'] = true;
                    break;
                }
            }
            // Sauvegarde de l'état mis à jour
            file_put_contents($fichier_commandes, json_encode($data_cmd, JSON_PRETTY_PRINT));
        }
    }
    
    // Utilisation du pattern PRG pour éviter la soumission multiple en cas de rafraîchissement
    header("Location: index.php"); 
    exit();
} else {
    // Redirection de sécurité si tentative d'accès frauduleux
    header("Location: Connexion.php");
    exit();
}
?>
