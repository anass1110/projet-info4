<?php 
// Initialisation de la session
// Ouvre l'accès aux variables d'état partagées avant l'envoi  html
session_start(); 
?>
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
        <form action="traitement_inscription.php" method="post" id="form-inscription">
            <fieldset>
                <legend>Créer votre compte SushyTech</legend>
                
                <?php // Zones de retour d'erreurs
                      // Contient  ?>
                <p id="erreur-js" class="msg-erreur cache"></p>
                <?php if(isset($_GET['erreur'])) echo "<p class='msg-erreur'>Veuillez remplir les champs obligatoires.</p>"; ?>
                
                <label for="login">Login (Email) :</label>
                <input type="email" id="login" name="user_login" maxlength="50" required>
                <span class="compteur-caracteres" data-cible="login">50 caractères restants</span>

                <label for="mdp">Mot de passe (Minimum 6 caractères) :</label>
                <input type="password" id="mdp" name="user_password" maxlength="32" required>
                <span class="compteur-caracteres" data-cible="mdp">32 caractères restants</span>

                <hr class="separateur">

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="user_nom" maxlength="30" required>
                <span class="compteur-caracteres" data-cible="nom">30 caractères restants</span>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="user_prenom" maxlength="30" required>
                <span class="compteur-caracteres" data-cible="prenom">30 caractères restants</span>

                <label for="pseudo">Pseudo :</label>
                <input type="text" id="pseudo" name="user_pseudo" maxlength="20">
                <span class="compteur-caracteres" data-cible="pseudo">20 caractères restants</span>

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
    <script src="scripts.js"></script> 
</body>
</html>
