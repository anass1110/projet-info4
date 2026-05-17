<?php
session_start();

// Restriction d'accès de base
// Bloque l'accès au formulaire si aucun identifiant d'utilisateur n'est enregistré en session
if (!isset($_SESSION['user'])) {
    header("Location: Connexion.php");
    exit();
}

$id_client_actuel = $_SESSION['user']['id'];
$commande_eligible = null;
$message_erreur_notation = "";

// Analyse du référentiel pour appliquer les critères du cahier des charges
$fichier_commandes = 'data/commandes.json';
if (file_exists($fichier_commandes)) {
    $data_cmd = json_decode(file_get_contents($fichier_commandes), true);
    $commandes_liste = $data_cmd['commandes'] ?? [];
    
    // Parcourt à l'envers pour trouver la DERNIÈRE commande passée par ce client
    $derniere_commande = null;
    for ($i = count($commandes_liste) - 1; $i >= 0; $i--) {
        if (isset($commandes_liste[$i]['id_client']) && $commandes_liste[$i]['id_client'] === $id_client_actuel) {
            $derniere_commande = $commandes_liste[$i];
            break; // On a trouvé la plus récente, on arrête la recherche
        }
    }
    
    // Application des filtres restrictifs du cahier des charges
    if ($derniere_commande === null) {
        $message_erreur_notation = "Vous n'avez pas encore passé de commande sur notre site.";
    } else {
        // Condition 1 : Pas de notation pour le sur place et à emporter
        if (isset($derniere_commande['type_commande']) && $derniere_commande['type_commande'] !== 'livraison') {
            $message_erreur_notation = "La notation est réservée exclusivement aux commandes livrées à domicile.";
        } 
        // Condition 2 : Uniquement si la livraison est validée et achevée
        elseif (isset($derniere_commande['statut']) && $derniere_commande['statut'] !== 'Livrée') {
            $message_erreur_notation = "Vous pourrez noter votre commande dès qu'elle vous aura été livrée (Statut actuel : " . htmlspecialchars($derniere_commande['statut']) . ").";
        } 
        // Condition 3 : L'utilisateur peut noter une seule fois chaque commande
        elseif (isset($derniere_commande['deja_note']) && $derniere_commande['deja_note'] === true) {
            $message_erreur_notation = "Vous avez déjà soumis votre avis unique pour votre dernière commande (N° " . htmlspecialchars($derniere_commande['id_commande']) . ").";
        } 
        // Tout est en ordre, la commande est validée pour notation
        else {
            $commande_eligible = $derniere_commande;
        }
    }
} else {
    $message_erreur_notation = "Erreur de chargement du module de notation.";
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
        
        <?php if (!empty($message_erreur_notation)): ?>
            <div class="box-grise" style="text-align: center; padding: 20px; max-width: 600px; margin: 40px auto; border: 2px solid #BC002D;">
                <p class="txt-alerte" style="font-weight: bold; font-size: 1.1em;"><?= $message_erreur_notation ?></p>
                <a href="accueil.php" class="bouton-nav" style="display: inline-block; margin-top: 15px;">Retour à l'accueil</a>
            </div>
        <?php else: ?>
            <form action="traitement_notation.php" method="post">
                <fieldset>
                    <legend>Votre avis sur la commande N° <?= htmlspecialchars($commande_eligible['id_commande']) ?></legend>
                    
                    <input type="hidden" name="id_commande" value="<?= htmlspecialchars($commande_eligible['id_commande']) ?>">

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
                    <textarea name="user_commentaire" rows="4" placeholder="Donnez-nous votre avis pour nous aider à nous améliorer..."></textarea>

                    <div class="actions-form">
                        <input type="submit" value="Envoyer mon avis" class="bouton-nav">
                    </div>
                </fieldset>
            </form>
        <?php endif; ?>
        
    </div>
    <script src="scripts.js"></script>
</body>
</html>
