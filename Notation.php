<?php
session_start();

// Restriction d'accès client
// Bloque l'accès au formulaire si aucun identifiant d'utilisateur n'est enregistré en session
if (!isset($_SESSION['user'])) {
    header("Location: Connexion.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Notation</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-formulaire">
        <form action="traitement_notation.php" method="post">
            <fieldset>
                <legend>Votre avis sur votre dernière commande</legend>

                <?php // Collecte des indicateurs de satisfaction
                      // Envoie des listes de choix numériques pour dissocier l'expérience produit et logistique ?>
                <label>Qualité des produits :</label>
                <select name="note_produit">
                    <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                    <option value="4">⭐⭐⭐⭐ - Très bon</option>
                    <option value="3">⭐⭐⭐ - Moyen</option>
                </select>

                <label>Qualité de la livraison :</label>
                <select name="note_livraison">
                    <option value="5">⭐⭐⭐⭐⭐ - Parfait</option>
                    <option value="4">⭐⭐⭐⭐ - Bien</option>
                    <option value="3">⭐⭐⭐ - Correct</option>
                </select>

                <label>Commentaire :</label>
                <textarea name="user_commentaire" rows="4"></textarea>

                <div class="actions-form">
                    <input type="submit" value="Envoyer mon avis" class="bouton-nav">
                </div>
            </fieldset>
        </form>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
