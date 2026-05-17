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
                
                //  Double vérification du statut côté serveur
                // On bloque si entre-temps la cuisine a démarré la préparation
                if (isset($commande['statut']) && !in_array($commande['statut'], ['En attente', 'A preparer'])) {
                    header("Location: Profil.php?erreur=deja_en_cuisine");
                    exit();
                }
                
                //  On vide le panier actuel du client pour y charger l'ancienne commande
                $_SESSION['panier'] = [];
                
                // On bascule le panier en "Mode Édition" en stockant les métadonnées de la commande
                $_SESSION['id_commande_en_modification'] = $commande['id_commande'];
                $_SESSION['total_deja_paye'] = floatval($commande['total']);
                
    
                // Chaque article retrouve sa structure pour être manipulable sur Produits.php et panier.php
                if (isset($commande['articles']) && is_array($commande['articles'])) {
                    foreach ($commande['articles'] as $art) {
                        
                        //  On teste toutes les variantes de clés possibles pour éviter bug de panier 
                        $id_art   = $art['id_article']   ?? $art['id']   ?? null;
                        $nom_art  = $art['nom_article']  ?? $art['nom']  ?? 'Produit inconnu';
                        $prix_art = $art['prix_unitaire'] ?? $art['prix'] ?? 0;
                        $qte_art  = $art['quantite']     ?? $art['qte']  ?? 1;
                        
                        // Si les valeurs de prix ou de nom étaient imbriquées plus profondément
                        if ($nom_art === 'Produit inconnu' && isset($art['informations']['nom'])) {
                            $nom_art = $art['informations']['nom'];
                        }
                        if ($prix_art == 0 && isset($art['informations']['prix'])) {
                            $prix_art = $art['informations']['prix'];
                        }

                        $_SESSION['panier'][] = [
                            "id" => $id_art,
                            "nom" => $nom_art,
                            "prix" => floatval($prix_art),
                            "quantite" => intval($qte_art),
                            "option" => $art['option_choisie'] ?? $art['option'] ?? ""
                        ];
                    }
                    
                    header("Location: panier.php");
                    exit();
                }
            }
        }
    }
    header("Location: Profil.php?erreur=echec");
    exit();
} else {
    header("Location: Connexion.php");
    exit();
}
?>
