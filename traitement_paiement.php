<?php
session_start();

// Contrôle d'accès, blocage des utilisateurs non authentifiés ou sans panier actif
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_carte'])) {
    
    // Le calcul du montant total actuel est effectué exclusivement côté serveur
    $total = 0;
    foreach ($_SESSION['panier'] as $article) {
        $total += $article['prix'] * $article['quantite'];
    }
    
    // Application de la réduction validée en session (uniquement pour les nouvelles commandes)
    if (!isset($_SESSION['id_commande_en_modification']) && isset($_SESSION['coupon'])) {
        if ($_SESSION['coupon']['type'] === 'pourcentage') {
            $total -= $total * ($_SESSION['coupon']['valeur'] / 100);
        } else {
            $total -= $_SESSION['coupon']['valeur'];
        }
    }
    $total = max(0, $total); 
    
    $fichier_cmd = 'data/commandes.json';
    $contenu_fichier = file_exists($fichier_cmd) ? file_get_contents($fichier_cmd) : '{"commandes":[],"paiements":[]}';
    $data_cmd = json_decode($contenu_fichier, true);

    // cas d'une modification de commande
    if (isset($_SESSION['id_commande_en_modification'])) {
        $id_cmd_cible = $_SESSION['id_commande_en_modification'];
        $ancien_total = $_SESSION['total_deja_paye'];
        $difference = $total - $ancien_total;

        foreach ($data_cmd['commandes'] as $i => $commande) {
            if ($commande['id_commande'] == $id_cmd_cible) {
                
                // Mise à jour des données de la commande modifiée
                $data_cmd['commandes'][$i]['articles'] = $_SESSION['panier'];
                $data_cmd['commandes'][$i]['total'] = floatval($total);
                $data_cmd['commandes'][$i]['date_modification'] = date("Y-m-d H:i:s");

                //  Si commande modifiée est plus chère -> Nouveau paiement de la différence
                if ($difference > 0) {
                    $nouveau_paiement_diff = [
                        "id_commande" => $id_cmd_cible,
                        "id_client"   => $_SESSION['user']['id'],
                        "montant"     => floatval($difference),
                        "carte_fin"   => "XXXX-XXXX-XXXX-" . substr($_POST['num_carte'], -4),
                        "type"        => "Ajustement (Complément)",
                        "date"        => date("Y-m-d H:i:s")
                    ];
                    $data_cmd['paiements'][] = $nouveau_paiement_diff;
                } 
                // Si commande modifiée est moins chère -> Génération d'un ticket de réduction
                elseif ($difference < 0) {
                    $valeur_avoir = abs($difference);
                    $code_avoir = "AVOIR-" . rand(100, 999);
                    
                    // On stocke le ticket généré en session pour qu'il soit utilisable lors du prochain panier
                    $_SESSION['coupon_avoir_genere'] = [
                        "code" => $code_avoir,
                        "type" => "valeur",
                        "valeur" => $valeur_avoir
                    ];
                }
                break;
            }
        }

        // Sauvegarde des modifications structurelles
        file_put_contents($fichier_cmd, json_encode($data_cmd, JSON_PRETTY_PRINT)); 

        // Sortie du mode édition et nettoyage de la session
        unset($_SESSION['id_commande_en_modification']);
        unset($_SESSION['type_commande_originale']);
        unset($_SESSION['total_deja_paye']);
        $_SESSION['panier'] = [];

        // Redirection vers le profil avec un indicateur adapté
        $status_redir = ($difference < 0) ? "success_mod_avoir" : "success_mod";
        header("Location: Profil.php?success=" . $status_redir);
        exit();
    }

    // cas d'une commande standard
    // Génération d'un identifiant de commande structuré
    $id_commande = "CMD-" . date("Ymd") . "-" . rand(1000, 9999);

    // Structuration de l'objet Commande initial
    $nouvelle_commande = [
        "id_commande"     => $id_commande,
        "id_client"       => $_SESSION['user']['id'], 
        "client"          => $_SESSION['user']['informations']['nom'] . " " . $_SESSION['user']['informations']['prenom'],
        "adresse"         => ($_POST['type_commande'] === 'livraison') ? $_SESSION['user']['informations']['adresse'] : "À emporter / Sur place",
        "telephone"       => $_SESSION['user']['informations']['telephone'],
        "articles"        => $_SESSION['panier'],
        "total"           => floatval($total),
        "type"            => $_POST['type_commande'],
        "heure_souhaitee" => !empty($_POST['heure_souhaitee']) ? $_POST['heure_souhaitee'] : "Dès que possible",
        "date_commande"   => date("Y-m-d H:i:s"),
        "statut"          => "En attente", // Statut initial d'attente requis pour l'édition dynamique
        "statut_paiement" => "Payé",
        "id_livreur"      => "" // Initialisation vide pour attribution ultérieure
    ];

    $nouveau_paiement = [
        "id_commande" => $id_commande,
        "id_client"   => $_SESSION['user']['id'],
        "montant"     => floatval($total),
        "carte_fin"   => "XXXX-XXXX-XXXX-" . substr($_POST['num_carte'], -4),
        "type"        => "Initial",
        "date"        => date("Y-m-d H:i:s")
    ];

    $data_cmd['commandes'][] = $nouvelle_commande;
    $data_cmd['paiements'][] = $nouveau_paiement;

    file_put_contents($fichier_cmd, json_encode($data_cmd, JSON_PRETTY_PRINT)); 

    // Nettoyage des données temporaires après validation
    $_SESSION['panier'] = [];
    unset($_SESSION['coupon']);

    header("Location: Profil.php?success=1");
    exit();
}

header("Location: accueil.php");
exit();
?>
