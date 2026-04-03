<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Connexion</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-formulaire">
        <form action="traitement_connexion.php" method="post">
            <fieldset>
                <legend>Connectez-vous</legend>
                <?php 
                if(isset($_GET['succes'])) echo "<p style='color:green; text-align:center;'>Inscription réussie, connectez-vous.</p>"; 
                if(isset($_GET['erreur'])) echo "<p style='color:red; text-align:center;'>Email ou mot de passe incorrect.</p>"; 
                ?>

                <label for="login-email">Adresse e-mail :</label>
                <input type="email" id="login-email" name="login-email" required>

                <label for="login-mdp">Mot de passe :</label>
                <input type="password" id="login-mdp" name="login-mdp" required>

                <div class="actions-form">
                    <input type="submit" value="Se connecter" class="bouton-nav">
                </div>
            </fieldset>
        </form>
    </div>
</body>
</html>