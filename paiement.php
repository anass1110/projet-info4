<?php
session_start();

// Restriction d'accès transactionnel
// Bloque le chargement du module bancaire si l'utilisateur n'est pas identifié ou si son panier est vide
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: index.php");
    exit();
}

// Recalcul de l'assiette financière côté serveur
// Détermine le montant total cumulé pour sécuriser l'affichage avant la transaction
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
        $montant_a_payer = 0; // Aucun encaissement supplémentaire requis
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
        <div class="box-paiement">
            <h2>💳 Finaliser ma commande</h2>
            
            <?php // Affichage dynamique du ticket de caisse selon le contexte (Nouvelle commande ou Ajustement) ?>
            <?php if ($mode_edition): ?>
                <div class="box-recap-edition">
                    <p class="txt-recap">Ancien total payé : <strong><?= number_format($ancien_total, 2) ?> €</strong></p>
                    <p class="txt-recap">Nouveau total : <strong><?= number_format($total, 2) ?> €</strong></p>
                    
                    <?php if ($montant_a_payer == 0 && $total < $ancien_total): ?>
                        <p class="txt-avoir">🎁 Un ticket de réduction de <?= number_format(abs($total - $ancien_total), 2) ?> € sera généré.</p>
                    <?php elseif ($montant_a_payer > 0): ?>
                        <p class="txt-complement">⚠️ Complément à payer : <?= number_format($montant_a_payer, 2) ?> €</p>
                    <?php else: ?>
                        <p class="txt-neutre">Aucun changement de montant.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <p class="total-regler">Reste à régler : <strong><?= number_format($montant_a_payer, 2) ?> €</strong></p>
            <p id="erreur-bancaire-js" class="msg-erreur-bancaire cache"></p>
            <hr>

            <form action="traitement_paiement.php" method="post" id="form-paiement">
                <?php // Persistance du contexte de livraison
                      // Transmet les paramètres logistiques issus du panier via des variables masquées (POST) ?>
                <input type="hidden" name="type_commande" value="<?= htmlspecialchars($_POST['type_commande'] ?? 'emporter') ?>">
                <input type="hidden" name="heure_souhaitee" value="<?= htmlspecialchars($_POST['heure_souhaitee'] ?? '') ?>">

                <?php // Bascule visuelle du TPE
                      // Demande les informations bancaires uniquement si un flux financier réel est nécessaire ?>
                <?php if ($montant_a_payer > 0): ?>
                    <label class="label-paiement">Nom sur la carte :</label>
                    <input type="text" name="nom_carte" placeholder="M. JEAN DUPONT" required class="input-paiement">

                    <label class="label-paiement">Numéro de carte (16 chiffres) :</label>
                    <input type="text" id="num_carte" name="num_carte" placeholder="1234567812345678" maxlength="16" required class="input-paiement input-carte">
                    <span class="compteur-caracteres compteur-carte" data-cible="num_carte">16 caractères restants</span>

                    <div class="flex-paiement">
                        <div>
                            <label class="label-paiement">Expiration (MM/AA) :</label>
                            <input type="text" id="exp_carte" name="exp" placeholder="12/28" maxlength="5" required class="input-paiement">
                            <span class="compteur-caracteres compteur-carte compteur-court" data-cible="exp_carte">5 caractères restants</span>
                        </div>
                        <div>
                            <label class="label-paiement">CVC (3 chiffres) :</label>
                            <input type="text" id="cvc_carte" name="cvc" placeholder="123" maxlength="3" required class="input-paiement">
                            <span class="compteur-caracteres compteur-carte compteur-court" data-cible="cvc_carte">3 caractères restants</span>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="nom_carte" value="Modification Sans Frais">
                    <input type="hidden" id="num_carte" name="num_carte" value="1111222233334444">
                    <input type="hidden" id="exp_carte" name="exp" value="12/99">
                    <input type="hidden" id="cvc_carte" name="cvc" value="123">
                <?php endif; ?>

                <div class="zone-btn-paiement">
                    <input type="submit" value="<?= $montant_a_payer > 0 ? 'Confirmer le paiement' : 'Valider la modification' ?>" class="btn-paiement">
                    <?php if ($montant_a_payer > 0): ?>
                        <p class="mention-secu">🔒 Paiement sécurisé via CYBank Gateway</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script src="scripts.js"></script> 
</body>
</html>
