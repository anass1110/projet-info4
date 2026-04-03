<?php
session_start();
// Sécurité : seul le restaurateur accède à cette page
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header("Location: accueil.php"); 
    exit();
}

// SIMULATION DE DONNÉES (Pour l'affichage de la Phase 2)
$commandes = [
    [
        'id_commande' => 'C1024',
        'statut' => 'A preparer',
        'type' => 'Livraison',
        'heure_souhaitee' => '19:45',
        'total' => 34.50,
        'client' => 'M. Le Breton'
    ],
    [
        'id_commande' => 'C1025',
        'statut' => 'En cours',
        'type' => 'À emporter',
        'heure_souhaitee' => '19:30',
        'total' => 22.00,
        'client' => 'Mme. Grignon'
    ],
    [
        'id_commande' => 'C1026',
        'statut' => 'En attente',
        'type' => 'Livraison',
        'heure_souhaitee' => '20:15',
        'total' => 56.10,
        'client' => 'Chef Sushy'
    ],
    [
        'id_commande' => 'C1027',
        'statut' => 'En livraison',
        'type' => 'Livraison',
        'heure_souhaitee' => '19:00',
        'total' => 15.00,
        'client' => 'Jean Dupont'
    ]
];

$statuts = [
    'A preparer' => 'À Préparer',
    'En cours'   => 'En Préparation',
    'En attente' => 'En Attente',
    'En livraison' => 'En Livraison'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Cuisine</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div id="gestion-commandes">
        <h2 style="text-align: center; margin-top: 20px; color: #800020;">Tableau de bord - Cuisine</h2>
        
        <div class="tableau-bord-container" style="display: flex; justify-content: space-around; padding: 20px; gap: 15px; align-items: flex-start;">
            
            <?php foreach ($statuts as $code_statut => $nom_statut): ?>
                <div class="colonne-commandes" style="flex: 1; background: #f4f4f4; border: 1px solid #1C1C1C; border-radius: 10px; padding: 15px; min-height: 400px;">
                    <h3 style="border-bottom: 2px solid #800020; padding-bottom: 10px; margin-bottom: 15px; color: #800020;"><?= $nom_statut ?></h3>
                    
                    <?php 
                    $trouve = false;
                    foreach($commandes as $c): 
                        if($c['statut'] === $code_statut): 
                            $trouve = true;
                    ?>
                        <div class="carte-commande" style="background: white; border: 1px solid #ddd; padding: 12px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <p style="margin: 0; font-weight: bold; color: #1C1C1C;">#<?= $c['id_commande'] ?> <span style="font-size: 0.8em; color: #666;">(<?= $c['type'] ?>)</span></p>
                            <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                            <p style="margin: 5px 0;"><small>🕒 Prévu pour : <strong><?= $c['heure_souhaitee'] ?></strong></small></p>
                            <p style="margin: 5px 0;"><small>👤 Client : <?= htmlspecialchars($c['client']) ?></small></p>
                            <p style="margin: 5px 0; font-weight: bold; color: #800020;"><?= number_format($c['total'], 2) ?>€</p>
                            
                            <div style="margin-top: 10px;">
                                <?php if($code_statut === 'A preparer'): ?>
                                    <button class="bouton-nav" style="width: 100%; font-size: 0.75em; background: #800020; color: white;">🔥 Commencer</button>
                                <?php elseif($code_statut === 'En cours'): ?>
                                    <button class="bouton-nav" style="width: 100%; font-size: 0.75em; border-color: orange;">⏸️ Pause</button>
                                    <button class="bouton-nav" style="width: 100%; font-size: 0.75em; margin-top: 5px;">✅ Prête</button>
                                <?php elseif($code_statut === 'En attente'): ?>
                                    <button class="bouton-nav" style="width: 100%; font-size: 0.75em;">▶️ Reprendre</button>
                                <?php elseif($code_statut === 'En livraison'): ?>
                                    <span style="color: blue; font-size: 0.8em; font-style: italic;">🚚 Coursier en route...</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        endif; 
                    endforeach; 

                    if(!$trouve): echo "<p style='color: gray; font-style: italic; font-size: 0.9em; text-align: center;'>Aucune commande</p>"; endif;
                    ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</body>
</html>