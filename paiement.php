<?php
session_start();
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

// Recalcul du total pour l'affichage visuel uniquement
$total = 0;
foreach ($_SESSION['panier'] as $article) { $total += $article['prix'] * $article['quantite']; }
if (isset($_SESSION['coupon'])) {
    if ($_SESSION['coupon']['type'] === 'pourcentage') { $total -= $total * ($_SESSION['coupon']['valeur'] / 100); } 
    else { $total -= $_SESSION['coupon']['valeur']; }
}
$total = max(0, $total);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Paiement Sécurisé</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div id="contenu-formulaire">
        <div style="background: white; padding: 30px; border-radius: 15px; border: 2px solid #1C1C1C; max-width: 500px; margin: 40px auto;">
            <h2 style="text-align: center; margin-top: 0;">💳 Finaliser ma commande</h2>
            
            <p style="text-align: center; font-size: 1.2em;">Total à régler : <strong><?= number_format($total, 2) ?> €</strong></p>
            <hr>

            <form action="traitement_paiement.php" method="post">
                <input type="hidden" name="type_commande" value="<?= htmlspecialchars($_POST['type_commande'] ?? 'emporter') ?>">
                <input type="hidden" name="heure_souhaitee" value="<?= htmlspecialchars($_POST['heure_souhaitee'] ?? '') ?>">

                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom sur la carte :</label>
                <input type="text" name="nom_carte" placeholder="M. JEAN DUPONT" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;">

                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Numéro de carte (16 chiffres) :</label>
                <input type="text" name="num_carte" pattern="\d{16}" title="16 chiffres requis" placeholder="1234567812345678" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; letter-spacing: 2px;">

                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="font-weight: bold;">Expiration :</label>
                        <input type="text" name="exp" placeholder="MM/AA" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: bold;">CVC :</label>
                        <input type="text" name="cvc" placeholder="123" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                </div>

                <div style="text-align: center;">
                    <input type="submit" value="Confirmer le paiement" class="bouton-nav" style="width: 100%; padding: 15px; background-color: #BC002D; color: white; border: none; cursor: pointer;">
                    <p style="font-size: 0.8em; color: gray; margin-top: 10px;">🔒 Paiement sécurisé via CYBank Gateway</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
