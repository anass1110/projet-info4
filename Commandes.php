<?php
session_start();

// Sécurité : seul le restaurateur accède à cette page
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header("Location: accueil.php"); 
    exit();
}

// --- CHARGEMENT DYNAMIQUE DES DONNÉES (PHASE 2) ---
$fichier_json = 'data/commandes.json';
$commandes = [];

if (file_exists($fichier_json)) {
    $donnees = json_decode(file_get_contents($fichier_json), true);
    $commandes = $donnees['commandes'] ?? [];
}

// Récupération de la liste des livreurs pour l'attribution (Affichage Phase 2)
$livreurs = [];
$fichier_users = 'data/utilisateurs.json';
if (file_exists($fichier_users)) {
    $data_users = json_decode(file_get_contents($fichier_users), true);
    foreach ($data_users['utilisateurs'] as $u) {
        if ($u['role'] === 'livreur') {
            $livreurs[] = $u;
        }
    }
}

$statuts = [
    'A preparer'   => 'À Préparer',
    'En cours'     => 'En Préparation',
    'En attente'   => 'En Attente',
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
        <h2 style="text-align: center; margin-top: 20px; color: #800020;">Tableau de bord - Cuisine (Temps Réel)</h2>
        
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
                            <p style="margin: 0; font-weight: bold; color: #1C1C1C;">#<?= $c['id_commande'] ?> <span style="font-size: 0.8em; color: #666;">(<?= ucfirst($c['type']) ?>)</span></p>
                            <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                            <p style="margin: 5px 0;"><small>🕒 Prévu pour : <strong><?= htmlspecialchars($c['heure_souhaitee']) ?></strong></small></p>
                            <p style="margin: 5px 0;"><small>👤 Client : <?= htmlspecialchars($c['client']) ?></small></p>
                            <p style="margin: 5px 0; font-weight: bold; color: #800020;"><?= number_format($c['total'], 2) ?>€</p>
                            
                            <?php if($c['type'] === 'livraison'): ?>
                                <div style="margin-top: 10px; border-top: 1px dashed #ccc; padding-top: 5px;">
                                    <label style="font-size: 0.7em; font-weight: bold;">Attribuer à :</label>
                                    <select style="width: 100%; font-size: 0.8em; padding: 3px;">
                                        <option value="">-- Choisir un livreur --</option>
                                        <?php foreach($livreurs as $l): ?>
                                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['informations']['prenom'] . " " . $l['informations']['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

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