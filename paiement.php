<?php
session_start();
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

$total = $_POST['total_commande'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_carte'])) {
    // 1. Récupération des données du fichier JSON
    $fichier_cmd = 'data/commandes.json';
    $data_cmd = json_decode(file_get_contents($fichier_cmd), true);
    
    // 2. Création de la nouvelle commande
    $heure_choisie = ($_POST['timing'] === 'immediate') ? "Dès que possible" : $_POST['heure_souhaitee'];
    
    $nouvelle_commande = [
        "id_commande" => "CMD-" . rand(1000, 9999),
        "client" => $_SESSION['user']['nom'] . " " . $_SESSION['user']['prenom'],
        "adresse" => $_SESSION['user']['adresse'] ?? "Sur place",
        "telephone" => $_SESSION['user']['telephone'] ?? "",
        "articles" => $_SESSION['panier'],
        "total" => floatval($_POST['total_paye']),
        "type" => $_POST['type_cmd'],
        "heure_souhaitee" => $heure_choisie,
        "statut" => "A preparer" // Exigé par la phase 2 pour l'affichage restaurateur
    ];

    // 3. Sauvegarde de la commande
    $data_cmd['commandes'][] = $nouvelle_commande;
    file_put_contents($fichier_cmd, json_encode($data_cmd, JSON_PRETTY_PRINT));
    
    // 4. On vide le panier après paiement
    $_SESSION['panier'] = []; 
    
    // 5. Redirection vers le profil
    header("Location: Profil.php?succes_paiement=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - API CYBank</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-formulaire">
        <h2 style="text-align:center; color:#1C1C1C;">Passerelle Sécurisée - <span style="color:#BC002D;">CYBank</span></h2>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <p>Montant à régler : <strong style="font-size: 1.5em;"><?= number_format($total, 2) ?>€</strong></p>
        </div>
        
        <form action="paiement.php" method="post">
            <input type="hidden" name="total_paye" value="<?= htmlspecialchars($total) ?>">
            <input type="hidden" name="type_cmd" value="<?= htmlspecialchars($_POST['type_commande'] ?? 'emporter') ?>">
            <input type="hidden" name="timing" value="<?= htmlspecialchars($_POST['timing'] ?? 'immediate') ?>">
            <input type="hidden" name="heure_souhaitee" value="<?= htmlspecialchars($_POST['heure_souhaitee'] ?? '') ?>">
            
            <label>Numéro de Carte Bancaire :</label>
            <input type="text" name="num_carte" placeholder="0000 0000 0000 0000" required maxlength="16">
            
            <label>Date d'expiration (MM/AA) :</label>
            <input type="text" placeholder="12/26" required maxlength="5">
            
            <label>Cryptogramme (CVC) :</label>
            <input type="text" placeholder="123" required maxlength="3">
            
            <div class="actions-form">
                <input type="submit" class="bouton-nav" value="Confirmer et Payer">
            </div>
        </form>
    </div>
</body>
</html>