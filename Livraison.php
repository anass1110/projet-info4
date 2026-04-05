<?php
session_start();

// Sécurité : seul le livreur accède à cette page
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header("Location: accueil.php"); 
    exit();
}

// Chargement dynamique des commandes depuis le fichier JSON
$fichier_cmd = 'data/commandes.json';
$commandes = [];

if (file_exists($fichier_cmd)) {
    $donnees = json_decode(file_get_contents($fichier_cmd), true);
    $commandes = $donnees['commandes'] ?? [];
}
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
        <h2 style="text-align: center; color: #1C1C1C;">Commandes à livrer</h2>
        
        <?php 
        $nb_livraisons = 0;
        foreach($commandes as $c): 
            if($c['statut'] === 'En livraison'): 
                $nb_livraisons++;
        ?>
        <div class="carte-livraison" style="border:2px solid #1C1C1C; padding:15px; margin-bottom:15px; background:white; border-radius: 10px;">
            <p><strong>Commande :</strong> <?= htmlspecialchars($c['id_commande']) ?></p>
            <p><strong>Client :</strong> <?= htmlspecialchars($c['client']) ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($c['telephone']) ?></p> 
            <p><strong>Adresse :</strong> <?= htmlspecialchars($c['adresse']) ?></p> 
            
            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($c['adresse']) ?>" target="_blank" class="bouton-maps">
                🗺️ Ouvrir l'itinéraire
            </a> 

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button class="bouton-confirmer">✅ Valider la livraison</button> 
                
                <button class="bouton-confirmer" style="background-color: #E67E22; font-size: 1.1em; padding: 15px;">
                    ⚠️ Abandonner (Adresse introuvable)
                </button>
            </div>
        </div>
        <?php 
            endif; 
        endforeach; 

        if($nb_livraisons === 0) {
            echo "<p style='text-align:center; font-style:italic; color:gray;'>Aucune commande en cours de livraison.</p>";
        }
        ?>
    </div>
</body>
</html>