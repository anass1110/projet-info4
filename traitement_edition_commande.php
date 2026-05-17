<?php
session_start();

// Protection du script
// Interdit l'accès si la requête n'est pas un POST ou si l'utilisateur n'est pas connecté
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user']) && isset($_POST['id_commande_edition'])) {
    
    $id_cmd_cible = $_POST['id_commande_edition'];
    $fichier_commandes = 'data/commandes.json';
    
    if (file_exists($fichier_commandes)) {
        $data_cmd = json_decode(file_get_contents($fichier_commandes), true);
        $commandes_liste = $data_cmd['commandes'] ?? [];
        
        // Recherche de la commande à modifier pour en extraire les produits
        foreach ($commandes_liste as $commande) {
            if ($commande['id_commande'] == $id_cmd_cible) {
                
                // Double vérification du statut côté serveur
                // On bloque si entre-temps la cuisine a démarré la préparation
                if (isset($commande['statut']) && $commande['statut'] !== 'En attente') {
                    header("Location: Profil.php?erreur=deja_en_cours");
                    exit();
                }
                
                //  On vide le panier actuel du client pour y charger l'ancienne commande
                $_SESSION['panier'] = [];
                
                //  On bascule le panier en "Mode Édition" en stockant les métadonnées de la commande
                $_SESSION['id_commande_en_modification'] = $commande['id_commande'];
                $_SESSION['type_commande_originale'] = $commande['type_commande'] ?? 'emporter';
                $_SESSION['total_deja_paye'] = floatval($commande['total']);
                
               
                // Chaque article retrouve sa structure pour être manipulable sur Produits.php et panier.php
                if (isset($commande['articles']) && is_array($commande['articles'])) {
                    foreach ($commande['articles'] as $art) {
                        $_SESSION['panier'][] = [
                            "id" => $art['id_article'],
                            "nom" => $art['nom_article'],
                            "prix" => floatval($art['prix_unitaire']),
                            "quantite" => intval($art['quantite']),
                            "option" => $art['option_choisie'] ?? ""
                        ];
                    }
                }
                
                // Redirection vers le panier pour que le client puisse ajouter ou enlever des produits
                header("Location: panier.php");
                exit();
            }
        }
    }
    
    header("Location: Profil.php");
    exit();
} else {
    header("Location: Connexion.php");
    exit();
}
?>
