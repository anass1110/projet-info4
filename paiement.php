<?php
session_start();

// Restriction d'accès transactionnel
// Bloque le chargement du module bancaire si l'utilisateur n'est pas identifié ou si son panier est vide
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

// Recalcul de l'assiette financière côté serveur
// Détermine le montant total brut cumulé pour sécuriser l'affichage avant la transaction
$total = 0;
foreach ($_SESSION['panier'] as $article) { 
    $total += $article['prix'] * $article['quantite']; 
}

$montant_a_payer = $total;
$mode_edition = false;
$ancien_total = 0;

// Logique d'ajustement pour modification de commande
if (isset($_SESSION['id_commande_en_modification'])) {
    $mode_edition = true;
    $ancien_total = $_SESSION['total_deja_paye'];
    $difference = $total - $ancien_total;
    
    if ($difference > 0) {
        $montant_a_payer = $difference;
    } else {
        $montant_a_payer = 0; // Rien à payer en plus
    }
} else {
    // Application du barème de réduction en session
    // Déduit la valeur correspondante au coupon (taux ou montant fixe) sur le total calculé
    if (isset($_SESSION['coupon'])) {
        if ($_SESSION['coupon']['type'] === 'pourcentage') { 
            $montant_a_payer -= $montant_a_payer * ($_SESSION['coupon']['valeur'] / 100); 
        } else { 
            $montant_a_payer -= $_SESSION['coupon']['valeur']; 
        }
    }
}

// Protection contre les valeurs négatives
$montant_a_payer = max(0, $montant_a_payer);
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
            
            <?php if ($mode_edition): ?>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; border: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0;">Ancien total payé : <strong><?= number_format($ancien_total, 2) ?> €</strong></p>
                    <p style="margin: 0 0 5px 0;">Nouveau total : <strong><?= number_format($total, 2) ?> €</strong></p>
                    
                    <?php if ($montant_a_payer == 0 && $total < $ancien_total): ?>
                        <p style="color: green; margin: 10px 0 0 0; font-weight: bold;">🎁 Un ticket de réduction de <?= number_format(abs($total - $ancien_total), 2) ?> € sera généré.</p>
                    <?php elseif ($montant_a_payer > 0): ?>
                        <p style="color: #BC002D; margin: 10px 0 0 0; font-weight: bold;">⚠️ Complément à payer : <?= number_format($montant_a_payer, 2) ?> €</p>
                    <?php else: ?>
                        <p style="color: gray; margin: 10px 0 0 0; font-weight: bold;">Aucun changement de montant.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <p style="text-align: center; font-size: 1.2em;">Reste à régler : <strong><?= number_format($montant_a_payer, 2) ?> €</strong></p>
            <p id="erreur-bancaire-js" class="msg-erreur cache" style="color:red; text-align:center; font-weight:bold; margin-bottom:15px;"></p>
            <hr>

            <form action="traitement_paiement.php" method="post" id="form-paiement">
                <?php // Persistance du contexte de livraison
                      // Transmet les paramètres logistiques issus du panier via des variables masquées (POST) ?>
                <input type="hidden" name="type_commande" value="<?= htmlspecialchars($_POST['type_commande'] ?? 'emporter') ?>">
                <input type="hidden" name="heure_souhaitee" value="<?= htmlspecialchars($_POST['heure_souhaitee'] ?? '') ?>">

                <?php if ($montant_a_payer > 0): ?>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom sur la carte :</label>
                    <input type="text" name="nom_carte" placeholder="M. JEAN DUPONT" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;">

                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Numéro de carte (16 chiffres) :</label>
                    <input type="text" id="num_carte" name="num_carte" placeholder="1234567812345678" maxlength="16" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; letter-spacing: 2px;">
                    <span class="compteur-caracteres" data-cible="num_carte" style="display:block; font-size:0.8em; color:gray; margin-bottom:15px;">16 caractères restants</span>

                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <label style="font-weight: bold;">Expiration (MM/AA) :</label>
                            <input type="text" id="exp_carte" name="exp" placeholder="12/28" maxlength="5" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <span class="compteur-caracteres" data-cible="exp_carte" style="display:block; font-size:0.8em; color:gray;">5 caractères restants</span>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-weight: bold;">CVC (3 chiffres) :</label>
                            <input type="text" id="cvc_carte" name="cvc" placeholder="123" maxlength="3" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <span class="compteur-caracteres" data-cible="cvc_carte" style="display:block; font-size:0.8em; color:gray;">3 caractères restants</span>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="nom_carte" value="Modification Sans Frais">
                    <input type="hidden" id="num_carte" name="num_carte" value="1111222233334444">
                    <input type="hidden" id="exp_carte" name="exp" value="12/99">
                    <input type="hidden" id="cvc_carte" name="cvc" value="123">
                <?php endif; ?>

                <div style="text-align: center;">
                    <input type="submit" value="<?= $montant_a_payer > 0 ? 'Confirmer le paiement' : 'Valider la modification' ?>" class="bouton-nav" style="width: 100%; padding: 15px; background-color: #BC002D; color: white; border: none; cursor: pointer;">
                    <?php if ($montant_a_payer > 0): ?>
                        <p style="font-size: 0.8em; color: gray; margin-top: 10px;">🔒 Paiement sécurisé via CYBank Gateway</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script src="scripts.js"></script> 
</body>
</html>
