<?php
session_start();
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
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
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-produits">
        <h2 class="titre-menu">Nos Menus Spéciaux</h2>
        <div class="grid-menus">
            <?php foreach ($menus as $m): ?>
            <div class="plat menu-special">
                <h4><?= htmlspecialchars($m['nom']) ?></h4>
                <p style="font-style: italic; font-size: 0.9em;"><?= htmlspecialchars($m['description']) ?></p>
                <div class="inclus-menu">
                    <p style="margin: 5px 0;"><strong>Inclus :</strong> <?= htmlspecialchars(implode(', ', $m['liste_plats'])) ?></p>
                </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
