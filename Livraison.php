<?php
session_start();

// Contrôle d'accès back-office
// Restreint l'accès à l'interface aux seuls comptes possédant le rôle de livreur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header("Location: accueil.php"); 
    exit();
}

// Chargement du registre des commandes
// Extrait la liste des transactions pour lister les livraisons à effectuer
$fichier_cmd = 'data/commandes.json';
$commandes = file_exists($fichier_cmd) ? json_decode(file_get_contents($fichier_cmd), true)['commandes'] ?? [] : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Livreur</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body class="mobile-view">
    <?php include('includes/header.php'); ?>

    <div id="infos-livraison">
        <h2 class="titre-livraison">Commandes à livrer</h2>
        
        <?php 
        // Filtrage des courses attribuées
        // Initialise le compteur et parcourt les commandes pour isoler celles affectées au livreur connecté
        $nb_livraisons = 0;
        foreach($commandes as $c): 
            if($c['statut'] === 'En livraison' && isset($c['id_livreur']) && $c['id_livreur'] === $_SESSION['user']['id']): 
                $nb_livraisons++;
        ?>
        <div class="carte-livraison carte-livreur">
            <p><strong>Commande :</strong> <?= htmlspecialchars($c['id_commande']) ?></p>
            <p><strong>Client :</strong> <?= htmlspecialchars($c['client']) ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($c['telephone']) ?></p> 
            <p><strong>Adresse :</strong> <?= htmlspecialchars($c['adresse']) ?></p> 
            
            <?php // Redirection vers le service de cartographie
                  // Génère un lien externe sécurisé en encodant l'adresse pour le calcul d'itinéraire ?>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($c['adresse']) ?>" target="_blank" class="bouton-maps">🗺️ Ouvrir l'itinéraire</a> 

            <?php // Bloc d'interception javascript
                  // Conteneur identifié par l'ID de commande pour la mise à jour asynchrone des statuts ?>
            <div id="actions-<?= htmlspecialchars($c['id_commande']) ?>" class="actions-livreur">
                <button class="bouton-confirmer btn-action-livreur" data-id="<?= htmlspecialchars($c['id_commande']) ?>" data-action="valider">✅ Valider la livraison</button> 
                <button class="bouton-confirmer btn-action-livreur btn-abandon" data-id="<?= htmlspecialchars($c['id_commande']) ?>" data-action="abandonner">⚠️ Abandonner (Adresse introuvable)</button>
            </div>
        </div>
        <?php 
            endif; 
        endforeach; 

        // Gestion du cas de tournée vide
        // Affiche un message de confirmation si aucune course active n'est associée à cet identifiant
        if($nb_livraisons === 0) { 
            echo "<p class='vide-livraison'>🎉 Aucune livraison en cours !</p>"; 
        }
        ?>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
