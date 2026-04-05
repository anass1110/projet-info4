<?php
session_start();

// Initialisation de la structure du panier si elle n'existe pas dans la session courante
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Chargement du référentiel pour vérification de la validité des coupons
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
$coupons_valides = $donnees_menu['coupons'] ?? [];

// Interception des actions soumises via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'ajouter') {
        $trouve = false;
        
        // Parcours du panier pour mettre à jour la quantité si l'article est déjà présent
        for ($i = 0; $i < count($_SESSION['panier']); $i++) {
            if ($_SESSION['panier'][$i]['id'] === $_POST['id_article']) {
                $_SESSION['panier'][$i]['quantite'] += intval($_POST['quantite']);
                $trouve = true;
                break; 
            }
        }
        
        // Insertion d'une nouvelle entrée si l'article est absent du panier
        if (!$trouve) {
            $_SESSION['panier'][] = [
                'id' => $_POST['id_article'],
                'nom' => $_POST['nom_article'],
                'prix' => floatval($_POST['prix']),
                'quantite' => intval($_POST['quantite']),
                'option' => $_POST['option_choisie'] ?? ''
            ];
        }
        
    } elseif ($_POST['action'] == 'appliquer_coupon') {
        // Normalisation de la saisie utilisateur (casse et espaces)
        $code_saisi = strtoupper(trim($_POST['code_coupon'])); 
        
        // Vérification de l'existence du coupon dans le référentiel
        foreach ($coupons_valides as $c) {
            if ($c['code'] === $code_saisi) {
                $_SESSION['coupon'] = $c;
                break;
            }
        }
    }
}

// Gestion des actions via requêtes GET
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    unset($_SESSION['coupon']); 
}
if (isset($_GET['retirer_coupon'])) {
    unset($_SESSION['coupon']);
}

// Implémentation du Pattern PRG (Post/Redirect/Get)
header("Location: panier.php"); 
exit();
?>
