<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Inscription</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-formulaire">
        <form action="traitement_inscription.php" method="post">
            <fieldset>
                <legend>Créer votre compte SushyTech</legend>
                <?php if(isset($_GET['erreur'])) echo "<p style='color:red; text-align:center;'>Veuillez remplir les champs obligatoires.</p>"; ?>
                
                <label for="login">Login (Email) :</label>
                <input type="email" id="login" name="user_login" required>

                <label for="mdp">Mot de passe :</label>
                <input type="password" id="mdp" name="user_password" required>

                <hr style="margin: 20px 0;">

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="user_nom" required>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="user_prenom" required>

                <label for="pseudo">Pseudo :</label>
                <input type="text" id="pseudo" name="user_pseudo">

                <label for="naissance">Date de naissance :</label>
                <input type="date" id="naissance" name="user_naissance">

                <label for="tel">Numéro de téléphone :</label>
                <input type="tel" id="tel" name="user_tel">

                <label for="adresse">Adresse de livraison :</label>
                <textarea id="adresse" name="user_adresse" rows="2"></textarea>

                <div class="actions-form">
                    <input type="submit" value="S'inscrire" class="bouton-nav">
                </div>
            </fieldset>
        </form>
    </div>
</body>
</html>