<?php
// Gestion de la session active
// Démarre l'espace mémoire pour manipuler les articles et coupons de l'utilisateur
session_start();
$total_brut = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Mon Panier</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div id="contenu-panier">
        <h2 class="titre-panier">Votre Panier</h2>
        
        <?php // Évaluation du contenu du panier
              // Propose un lien vers le catalogue si aucun produit n'est stocké en session ?>
        <?php if (empty($_SESSION['panier'])): ?>
            <p class="panier-vide">Votre panier est actuellement vide.</p>
            <div class="zone-bouton-vide">
                <a href="Produits.php" class="bouton-nav">Voir la carte</a>
            </div>
        <?php else: ?>
            <table class="table-panier">
                <thead>
                    <tr class="entete-tableau">
                        <th>Article</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                <?php // Énumération des lignes du panier
                      // Calcule le coût par ligne et cumule le montant total brut de la commande ?>
                <?php foreach ($_SESSION['panier'] as $article): 
                    $st = $article['prix'] * $article['quantite'];
                    $total_brut += $st;
                ?>
                <tr>
                    <td class="cell-article">
                        <strong><?= htmlspecialchars($article['nom']) ?></strong>
                        <?php if(!empty($article['option'])): ?>
                            <br><small class="option-article">(Option : <?= htmlspecialchars($article['option']) ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td class="cell-centre"><?= number_format($article['prix'], 2) ?>€</td>
                    <td class="cell-centre"><?= $article['quantite'] ?></td>
                    <td class="cell-droite"><?= number_format($st, 2) ?>€</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php
            // Calcul des remises commerciales
            // Évalue les règles du coupon en session (déduction fixe ou pourcentage) pour définir la réduction
            $reduction = 0;
            if (isset($_SESSION['coupon'])) {
                if ($_SESSION['coupon']['type'] === 'pourcentage') {
                    $reduction = $total_brut * ($_SESSION['coupon']['valeur'] / 100);
                } else {
                    $reduction = $_SESSION['coupon']['valeur'];
                }
            }
            // Sécurité financière : empêche un total négatif si la remise dépasse le montant brut
            $total_final = max(0, $total_brut - $reduction);
            ?>

            <div class="bloc-recap-panier">
                <form action="traitement_panier.php" method="post" class="form-coupon">
                    <input type="hidden" name="action" value="appliquer_coupon">
                    <input type="text" name="code_coupon" placeholder="Code promo" class="input-coupon">
                    <input type="submit" value="Appliquer" class="bouton-nav">
                </form>

                <p class="txt-total-partiel">Total partiel : <?= number_format($total_brut, 2) ?>€</p>
                
                <?php if(isset($_SESSION['coupon'])): ?>
                    <p class="txt-reduction">
                        Réduction (<?= htmlspecialchars($_SESSION['coupon']['code']) ?>) : -<?= number_format($reduction, 2) ?>€ 
                        <a href="traitement_panier.php?retirer_coupon=1" class="lien-supprimer-coupon">[Supprimer]</a>
                    </p>
                <?php endif; ?>

                <h2 class="total-a-payer">Total à payer : <?= number_format($total_final, 2) ?>€</h2>
                <a href="traitement_panier.php?vider=1" class="lien-vider">Vider mon panier</a>
            </div>

            <hr class="separation-panier">

            <div class="bloc-validation-panier">
                <h3 class="titre-validation">Validation de la commande</h3>
                
                <?php // Vérification du statut d'authentification
                      // Impose la connexion au site avant d'autoriser l'accès au formulaire de paiement ?>
                <?php if(!isset($_SESSION['user'])): ?>
                    <p class="alerte-connexion">
                        Vous devez être <a href="Connexion.php">connecté</a> pour finaliser votre commande.
                    </p>
                <?php else: ?>
                    <form action="paiement.php" method="post" class="form-validation">
                        <div class="champ-formulaire">
                            <label>Mode de retrait :</label>
                            <select name="type_commande" required>
                                <option value="emporter">À emporter</option>
                                <option value="livraison">Livraison</option>
                            </select>
                        </div>
                        <div class="champ-formulaire">
                            <label>Quand préparer ?</label>
                            <select name="timing" required>
                                <option value="immediate">Immédiatement</option>
                                <option value="plus_tard">Pour plus tard</option>
                            </select>
                        </div>
                        <div class="champ-formulaire">
                            <label>Heure souhaitée (si différé) :</label>
                            <input type="time" name="heure_souhaitee">
                        </div>
                        <div class="zone-soumission">
                            <input type="submit" class="bouton-nav bouton-valider" value="Procéder au paiement">
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
