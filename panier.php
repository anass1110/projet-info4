<?php
session_start();
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajout d'un article au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $item = [
        'id' => $_POST['id_article'],
        'nom' => $_POST['nom_article'],
        'prix' => floatval($_POST['prix']),
        'quantite' => intval($_POST['quantite'])
    ];
    $_SESSION['panier'][] = $item;
    header("Location: Produits.php"); // Retourne au menu après ajout
    exit();
}

// Vider le panier
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header("Location: panier.php");
    exit();
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Panier</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-formulaire" style="width: 70%;">
        <h2>Votre Panier</h2>
        
        <?php if (empty($_SESSION['panier'])): ?>
            <p style="text-align:center;">Votre panier est vide.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Article</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
                <?php foreach ($_SESSION['panier'] as $article): 
                    $sous_total = $article['prix'] * $article['quantite'];
                    $total += $sous_total;
                ?>
                <tr>
                    <td><?= htmlspecialchars($article['nom']) ?></td>
                    <td><?= number_format($article['prix'], 2) ?>€</td>
                    <td><?= $article['quantite'] ?></td>
                    <td><?= number_format($sous_total, 2) ?>€</td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h3 style="text-align: right; color: #BC002D;">Total : <?= number_format($total, 2) ?>€</h3>
            <div style="text-align: right;">
                <a href="panier.php?vider=1" class="bouton-nav" style="border-color: red; color: red;">Vider le panier</a>
            </div>

            <hr style="margin: 30px 0;">

            <h3>Validation de la commande</h3>
            <?php if(!isset($_SESSION['user'])): ?>
                <p style="color: red;">Vous devez être <a href="Connexion.php">connecté</a> pour valider votre commande.</p>
            <?php else: ?>
                <form action="paiement.php" method="post">
                    <input type="hidden" name="total_commande" value="<?= $total ?>">
                    
                    <label>Mode de retrait :</label>
                    <select name="type_commande" required>
                        <option value="emporter">À emporter</option>
                        <option value="livraison">Livraison</option>
                    </select>

                    <label>Préparation :</label>
                    <select name="timing" required>
                        <option value="immediate">Immédiate</option>
                        <option value="plus_tard">Pour plus tard</option>
                    </select>

                    <label>Heure souhaitée (si "Pour plus tard") :</label>
                    <input type="time" name="heure_souhaitee">

                    <div class="actions-form">
                        <input type="submit" class="bouton-nav" value="Payer avec CYBank">
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>