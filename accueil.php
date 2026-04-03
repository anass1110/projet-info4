<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Accueil</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div id="contenu-principal">
        <div class="recherche">
            <h2>Que voulez-vous manger aujourd'hui ?</h2>
            <form action="Produits.php" method="get">
                <input type="text" name="recherche" id="champ-recherche" placeholder="Chercher un plat...">
                <input type="submit" value="Rechercher" class="bouton-nav">
            </form>
        </div>

        <div class="plats-vedettes">
            <h2>Nos plats du jour & Favoris</h2>
            <ul>
                <li>Sushi Saumon (Favori)</li>
                <li>Ramen Boeuf (Plat du jour)</li>
                <li>Maki California (Incontournable)</li>
            </ul>
        </div>
    </div>
</body>
</html>