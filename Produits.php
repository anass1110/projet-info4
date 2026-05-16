<?php
session_start();
$donnees_menu = file_exists('data/menu.json') ? json_decode(file_get_contents('data/menu.json'), true) : [];
$plats = $donnees_menu['plats'] ?? [];
$menus = $donnees_menu['menus'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Notre Menu</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body class="<?= (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'theme-sombre' : '' ?>">
    <?php include('includes/header.php'); ?>
    <div id="contenu-produits">
        
        <div class="recherche" style="text-align: center; margin-bottom: 30px;">
            <input type="text" id="champ-recherche" placeholder="Recherche en direct un sushi..." style="padding: 10px; width: 50%; border-radius: 20px; border: 2px solid #1C1C1C;">
        </div>

        <h2 class="titre-menu">Nos Menus Spéciaux</h2>
        <div class="grid-menus">
            <?php foreach ($menus as $m): ?>
            <div class="plat menu-special">
                <h4><?= htmlspecialchars($m['nom']) ?></h4>
                <p style="font-style: italic; font-size: 0.9em;"><?= htmlspecialchars($m['description']) ?></p>
                <div class="inclus-menu">
                    <p style="margin: 5px 0;"><strong>Inclus :</strong> <?= htmlspecialchars(implode(', ', $m['liste_plats'])) ?></p>
                </div>
                <p class="texte-bleu txt-gras"><?= number_format($m['prix'], 2) ?>€</p>
                
                <form action="traitement_panier.php" method="post" class="form-ajout">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id_article" value="<?= $m['id'] ?>">
                    <input type="hidden" name="nom_article" value="<?= htmlspecialchars($m['nom']) ?>">
                    <input type="hidden" name="prix" value="<?= $m['prix'] ?>">
                    <div class="box-quantite">
                        <input type="number" name="quantite" class="input-qty" value="1" min="1">
                        <input type="submit" class="bouton-nav" value="Ajouter">
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <hr class="separateur">

        <h2 class="titre-menu">Nos Plats à la Carte</h2>
        <div class="grid-menus" id="zone-catalogue">
            <?php foreach ($plats as $p): ?>
            <div class="plat">
                <h4><?= htmlspecialchars($p['nom']) ?></h4>
                <p><?= htmlspecialchars($p['description']) ?></p>
                <p class="texte-bleu txt-gras"><?= number_format($p['prix'], 2) ?>€</p>
                
                <form action="traitement_panier.php" method="post" class="form-ajout">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id_article" value="<?= $p['id'] ?>">
                    <input type="hidden" name="nom_article" value="<?= htmlspecialchars($p['nom']) ?>">
                    <input type="hidden" name="prix" value="<?= $p['prix'] ?>">
                    
                    <?php if (!empty($p['options_possibles'])): ?>
                        <select name="option_choisie" class="select-perso">
                            <option value="">-- Options --</option>
                            <?php foreach($p['options_possibles'] as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    
                    <div class="box-quantite">
                        <input type="number" name="quantite" class="input-qty" value="1" min="1">
                        <input type="submit" class="bouton-nav" value="Ajouter">
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
