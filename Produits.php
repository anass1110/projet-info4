<?php
session_start();
// Lecture des données JSON
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
$plats = $donnees_menu['plats'] ?? [];
$menus = $donnees_menu['menus'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Notre Menu</title>
    <link rel="stylesheet" type="text/css" href="fichier.css?v=<?= time() ?>">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div id="contenu-produits">
        <h2 style="width:100%; text-align:center;">Nos Menus Spéciaux</h2>
        <div class="grid-menus">
            <?php foreach ($menus as $m): ?>
            <div class="plat menu-special">
                <h4><?= htmlspecialchars($m['nom']) ?></h4>
                <p style="font-style: italic; font-size: 0.9em;"><?= htmlspecialchars($m['description']) ?></p>
                <div style="background-color: #F4F1EA; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 0.85em; border: 1px solid #1C1C1C;">
                    <p style="margin: 5px 0;"><strong>Inclus :</strong> <?= htmlspecialchars(implode(', ', $m['liste_plats'])) ?></p>
                </div>
                <p style="font-size: 1.3em; margin: 10px 0; color: #BC002D; font-weight: bold;"><?= number_format($m['prix'], 2) ?>€</p>
                
                <form action="traitement_panier.php" method="post">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id_article" value="<?= $m['id'] ?>">
                    <input type="hidden" name="nom_article" value="<?= htmlspecialchars($m['nom']) ?>">
                    <input type="hidden" name="prix" value="<?= $m['prix'] ?>">
                    <input type="number" name="quantite" value="1" min="1" style="width:50px;">
                    <input type="submit" class="bouton-nav" value="Ajouter">
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 style="width:100%; text-align:center; margin-top:40px;">À la carte</h2>
        <div class="grid-plats">
            <?php 
            // Normalisation du paramètre de recherche pour comparaison
            $recherche = strtolower($_GET['recherche'] ?? ''); 
            
            foreach ($plats as $p): 
                // Logique de filtrage dynamique 
                if (!empty($recherche) && strpos(strtolower($p['nom']), $recherche) === false) {
                    continue; 
                }
            ?>
            <div class="plat">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" style="width:100%; height:180px; object-fit:cover; border-radius:8px;">
                <h4><?= htmlspecialchars($p['nom']) ?></h4>
                
                <div style="background-color: #F4F1EA; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85em; border: 1px solid #E0E0E0; text-align: left;">
                    <p style="margin: 3px 0;"><strong>🔥 Nutrition :</strong> <?= htmlspecialchars($p['nutrition'] ?? 'N/D') ?></p>
                    <p style="margin: 3px 0; color: #BC002D;"><strong>⚠️ Allergènes :</strong> <?= (isset($p['allergenes']) && is_array($p['allergenes'])) ? htmlspecialchars(implode(', ', $p['allergenes'])) : 'Aucun' ?></p>
                </div>

                <p style="color: #BC002D; font-weight: bold; font-size: 1.2em; margin-bottom: 15px;"><?= number_format($p['prix'], 2) ?>€</p>
                
                <form action="traitement_panier.php" method="post" style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id_article" value="<?= $p['id'] ?>">
                    <input type="hidden" name="nom_article" value="<?= htmlspecialchars($p['nom']) ?>">
                    <input type="hidden" name="prix" value="<?= $p['prix'] ?>">

                    <?php if (!empty($p['options_possibles'])): ?>
                        <select name="option_choisie" style="width: 80%; padding: 5px; border-radius: 5px;">
                            <option value="">-- Personnaliser --</option>
                            <?php foreach($p['options_possibles'] as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <div style="display: flex; gap: 10px;">
                        <input type="number" name="quantite" value="1" min="1" style="width:50px; text-align: center;">
                        <input type="submit" class="bouton-nav" value="Ajouter">
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
