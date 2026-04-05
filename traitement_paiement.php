<?php
session_start();

// Contrôle d'accès, blocage des utilisateurs non authentifiés ou sans panier actif
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_carte'])) {
    
    // Le calcul du montant total est effectué exclusivement côté serveur
    $total = 0;
    foreach ($_SESSION['panier'] as $article) {
        $total += $article['prix'] * $article['quantite'];
    }
    
    // Application de la réduction validée en session
    if (isset($_SESSION['coupon'])) {
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

    // Génération d'un identifiant de commande structuré
    $id_commande = "CMD-" . date("Ymd") . "-" . rand(1000, 9999);

    // Structuration de l'objet Commande
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
        "statut"          => "A preparer",
        "statut_paiement" => "Payé",
        "id_livreur"      => "" // Initialisation vide pour attribution ultérieure
    ];

    $nouveau_paiement = [
        "id_commande" => $id_commande,
        "id_client"   => $_SESSION['user']['id'],
        "montant"     => floatval($total),
        "carte_fin"   => "XXXX-XXXX-XXXX-" . substr($_POST['num_carte'], -4),
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
