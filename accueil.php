<?php 
// Initialisation de la session
// Démarre l'espace de stockage temporaire pour suivre l'état de l'utilisateur sur le site
session_start(); 
?>
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
        <?php // Barre de recherche principale
              // Redirige l'utilisateur vers le catalogue global via une méthode GET contenant le critère textuel ?>
        <div class="recherche">
            <h2>Que voulez-vous manger aujourd'hui ?</h2>
            <form action="Produits.php" method="get">
                <input type="text" name="recherche" id="champ-recherche" placeholder="Chercher un plat...">
                <input type="submit" value="Rechercher" class="bouton-nav">
            </form>
        </div>

        <?php // Section de mise en avant (Marketing)
              // Liste statique des produits populaires pour inciter à la navigation vers le catalogue ?>
        <div class="plats-vedettes">
            <h2>Nos plats du jour & Favoris</h2>
            <ul>
                <li>Sushi Saumon (Favori)</li>
                <li>Ramen Boeuf (Plat du jour)</li>
                <li>Maki California (Incontournable)</li>
            </ul>
        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
