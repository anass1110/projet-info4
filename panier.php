<?php
session_start();

// Initialisation du panier si inexistant
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Chargement des données du menu pour vérifier les coupons [cite: 8]
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
$coupons_valides = $donnees_menu['coupons'] ?? [];
$message_coupon = '';

// --- LOGIQUE : AJOUT D'UN ARTICLE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $item = [
        'id' => $_POST['id_article'],
        'nom' => $_POST['nom_article'],
        'prix' => floatval($_POST['prix']),
        'quantite' => intval($_POST['quantite']),
        'option' => $_POST['option_choisie'] ?? '' // Capture de la personnalisation
    ];
    $_SESSION['panier'][] = $item;
    header("Location: Produits.php");
    exit();
}

// --- LOGIQUE : APPLIQUER UN COUPON ---
if (isset($_POST['action']) && $_POST['action'] == 'appliquer_coupon') {
    $code_saisi = strtoupper(trim($_POST['code_coupon']));
    $trouve = false;
    foreach ($coupons_valides as $c) {
        if ($c['code'] === $code_saisi) {
            $_SESSION['coupon'] = $c;
            $message_coupon = "<span style='color:green;'>Coupon appliqué avec succès !</span>";
            $trouve = true;
            break;
        }
    }
    if (!$trouve) {
        $message_coupon = "<span style='color:red;'>Code promo invalide.</span>";
    }
}

// --- LOGIQUE : VIDER LE PANIER OU RETIRER LE COUPON ---
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    unset($_SESSION['coupon']);
    header("Location: panier.php");
    exit();
}
if (isset($_GET['retirer_coupon'])) {
    unset($_SESSION['coupon']);
    header("Location: panier.php");
    exit();
}

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

    <div id="contenu-formulaire" style="width: 80%; margin: 20px auto;">
        <h2 style="text-align: center;">Votre Panier</h2>
        
        <?php if (empty($_SESSION['panier'])): ?>
            <p style="text-align:center; padding: 20px;">Votre panier est actuellement vide.</p>
            <div style="text-align: center;">
                <a href="Produits.php" class="bouton-nav">Voir la carte</a>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Article</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Prix unitaire</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Quantité</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($_SESSION['panier'] as $article): 
                    $st = $article['prix'] * $article['quantite'];
                    $total_brut += $st;
                ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <strong><?= htmlspecialchars($article['nom']) ?></strong>
                        <?php if(!empty($article['option'])): ?>
                            <br><small style="color: #666; font-style: italic;">(Option : <?= htmlspecialchars($article['option']) ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;"><?= number_format($article['prix'], 2) ?>€</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;"><?= $article['quantite'] ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?= number_format($st, 2) ?>€</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php
            // Calcul de la réduction et du total final
            $reduction = 0;
            if (isset($_SESSION['coupon'])) {
                if ($_SESSION['coupon']['type'] === 'pourcentage') {
                    $reduction = $total_brut * ($_SESSION['coupon']['valeur'] / 100);
                } else {
                    $reduction = $_SESSION['coupon']['valeur'];
                }
            }
            $total_final = max(0, $total_brut - $reduction);
            ?>

            <div style="text-align: right; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                <form action="panier.php" method="post" style="margin-bottom: 10px;">
                    <input type="hidden" name="action" value="appliquer_coupon">
                    <input type="text" name="code_coupon" placeholder="Code promo" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <input type="submit" value="Appliquer" class="bouton-nav" style="padding: 8px 15px;">
                </form>
                <p><?= $message_coupon ?></p>

                <p style="font-size: 1.1em;">Total partiel : <?= number_format($total_brut, 2) ?>€</p>
                
                <?php if(isset($_SESSION['coupon'])): ?>
                    <p style="color: green; font-weight: bold;">
                        Réduction (<?= htmlspecialchars($_SESSION['coupon']['code']) ?>) : -<?= number_format($reduction, 2) ?>€ 
                        <a href="panier.php?retirer_coupon=1" style="color:red; font-size:0.8em; text-decoration: none; margin-left: 10px;">[Supprimer]</a>
                    </p>
                <?php endif; ?>

                <h2 style="color: #BC002D; margin-top: 10px;">Total à payer : <?= number_format($total_final, 2) ?>€</h2>
                <a href="panier.php?vider=1" style="color: #666; font-size: 0.9em;">Vider mon panier</a>
            </div>

            <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ddd;">

            <div style="max-width: 500px; margin-left: auto;">
                <h3 style="color: #BC002D;">Validation de la commande</h3>
                
                <?php if(!isset($_SESSION['user'])): ?>
                    <p style="color: red; background: #fff5f5; padding: 10px; border-radius: 5px;">
                        Vous devez être <a href="Connexion.php">connecté</a> pour finaliser votre commande.
                    </p>
                <?php else: ?>
                    <form action="paiement.php" method="post" style="display: flex; flex-direction: column; gap: 15px;">
                        <input type="hidden" name="total_commande" value="<?= $total_final ?>">
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Mode de retrait :</label>
                            <select name="type_commande" required style="width: 100%; padding: 8px;">
                                <option value="emporter">À emporter</option>
                                <option value="livraison">Livraison</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Quand préparer ?</label>
                            <select name="timing" required style="width: 100%; padding: 8px;">
                                <option value="immediate">Immédiatement</option>
                                <option value="plus_tard">Pour plus tard</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Heure souhaitée (si différé) :</label>
                            <input type="time" name="heure_souhaitee" style="width: 100%; padding: 8px;">
                        </div>

                        <div style="text-align: right; margin-top: 10px;">
                            <input type="submit" class="bouton-nav" value="Procéder au paiement" style="width: 100%; font-size: 1.1em; padding: 12px;">
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>