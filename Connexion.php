<?php 
// Initialisation de la session
// Démarre ou récupère la session active sur le serveur pour maintenir l'état de l'utilisateur
session_start(); 
?>
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
                // Traitement des notifications contextuelles
                // Intercepte les paramètres de retour d'URL (GET) pour afficher les messages d'état correspondants
                if(isset($_GET['succes'])) echo "<p class='msg-succes'>Inscription réussie, connectez-vous.</p>"; 
                if(isset($_GET['erreur'])) echo "<p class='msg-erreur'>Email ou mot de passe incorrect.</p>"; 
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
