<?php
session_start();

// Initialisation de la structure du panier si elle n'existe pas dans la session courante
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Chargement du référentiel pour vérification de la validité des coupons et sécurisation des prix
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
$coupons_valides = $donnees_menu['coupons'] ?? [];

// Interception des actions soumises via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'ajouter') {
        
        $id_interne = $_POST['id_article'];
        $quantite_saisie = intval($_POST['quantite']);
        $vrai_prix = 0;
        $vrai_nom = "";

        // Sécurité : On récupère le vrai prix et le vrai nom depuis le fichier JSON (évite la triche sur les prix)
        
        // 1. Recherche de l'identifiant dans la catégorie des plats
        if (isset($donnees_menu['plats'])) {
            foreach ($donnees_menu['plats'] as $p) {
                if ($p['id'] === $id_interne) {
                    $vrai_prix = floatval($p['prix']);
                    $vrai_nom = $p['nom'];
                    break;
                }
            }
        }
        
        // 2. Recherche de l'identifiant dans la catégorie des menus si non trouvé dans les plats
        if ($vrai_prix === 0 && isset($donnees_menu['menus'])) {
            foreach ($donnees_menu['menus'] as $m) {
                if ($m['id'] === $id_interne) {
                    $vrai_prix = floatval($m['prix']);
                    $vrai_nom = $m['nom'];
                    break;
                }
            }
        }

        // Si l'article n'existe pas dans le JSON, on bloque l'ajout pour des raisons de sécurité
        if ($vrai_prix === 0) {
            die("Erreur de sécurité : L'article demandé n'existe pas ou le prix a été modifié.");
        }

        $trouve = false;
        
        // Parcours du panier pour mettre à jour la quantité si l'article est déjà présent
        for ($i = 0; $i < count($_SESSION['panier']); $i++) {
            if ($_SESSION['panier'][$i]['id'] === $id_interne) {
                $_SESSION['panier'][$i]['quantite'] += $quantite_saisie;
                $trouve = true;
                break; 
            }
        }
        
        // Insertion d'une nouvelle entrée si l'article est absent du panier
        // Utilisation du nom et du prix du serveur pour empêcher l'injection de faux prix
        if (!$trouve) {
            $_SESSION['panier'][] = [
                'id' => $id_interne,
                'nom' => $vrai_nom,
                'prix' => $vrai_prix,
                'quantite' => $quantite_saisie,
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
