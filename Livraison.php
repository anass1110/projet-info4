<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header("Location: accueil.php"); exit();
}
$commandes = json_decode(file_get_contents('data/commandes.json'), true)['commandes'];
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
        <h2>Commandes à livrer</h2>
        <?php foreach($commandes as $c): if($c['statut'] === 'En livraison'): ?>
        <div class="carte-livraison" style="border:2px solid #1C1C1C; padding:15px; margin-bottom:15px; background:white;">
            <p><strong>Commande :</strong> <?= $c['id_commande'] ?></p>
            <p><strong>Client :</strong> <?= htmlspecialchars($c['client']) ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($c['telephone']) ?></p> 
            <p><strong>Adresse :</strong> <?= htmlspecialchars($c['adresse']) ?></p> 
            
            <a href="https://maps.google.com/?q=<?= urlencode($c['adresse']) ?>" target="_blank" class="bouton-maps">
                🗺️ Ouvrir Maps
            </a> 
            <button class="bouton-confirmer">Valider la livraison (Phase 3)</button> 
        </div>
        <?php endif; endforeach; ?>
    </div>
</body>
</html>