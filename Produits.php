<?php
session_start();

// Chargement des données du catalogue
// Récupère l'intégralité de la carte depuis le fichier de stockage json structurel
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
$plats = $donnees_menu['plats'] ?? [];
$plat_surprise = !empty($plats) ? $plats[array_rand($plats)] : null;   // Sélection aléatoire sécurisée d'un article unique du catalogue
$menus = $donnees_menu['menus'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Notre Menu</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body class="<?= (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'theme-sombre' : '' ?>">
    <?php include('includes/header.php'); ?>
    
    <div id="contenu-produits">
        
        <?php // Barre de recherche
              // Élément cible capturé par l'écouteur javascript pour le filtrage dynamique du catalogue ?>
        <div class="recherche-container">
            <input type="text" id="champ-recherche" placeholder="Recherche en direct un sushi..." class="input-recherche">
        </div>

        <h2>Nos Menus Spéciaux</h2>
        <div class="grid-menus">
            <?php // Génération de la section formules
                  // Parcourt les offres groupées pour assembler les blocs et lister les composants inclus ?>
            <?php foreach ($menus as $m): ?>
            <div class="plat menu-special">
                <h4><?= htmlspecialchars($m['nom']) ?></h4>
                <p class="desc-plat"><?= htmlspecialchars($m['description']) ?></p>
                <div class="box-grise">
                    <p class="m-0"><strong>Inclus :</strong> <?= htmlspecialchars(implode(', ', $m['liste_plats'])) ?></p>
                </div>
                <p class="prix-plat"><?= number_format($m['prix'], 2) ?>€</p>
                
                <form action="traitement_panier.php" method="post" class="form-ajout">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id_article" value="<?= $m['id'] ?>">
                    <input type="hidden" name="nom_article" value="<?= htmlspecialchars($m['nom']) ?>">
                    <input type="hidden" name="prix" value="<?= $m['prix'] ?>">
                    <div class="box-quantite">
                        <input type="number" name="quantite" value="1" min="1" class="input-qty">
                        <input type="submit" class="bouton-nav" value="Ajouter">
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="titre-menu marge-top">À la carte</h2>
        <?php // Conteneur cible pour injection asynchrone
              // Cet identifiant id="zone-catalogue" est vidé et reconstruit par le script de recherche dynamique  ?>
        <div class="grid-plats" id="zone-catalogue">
            <?php // Génération des articles individuels
                  // Construit les fiches produits avec leurs spécificités nutritionnelles, allergènes et options ?>
            <?php foreach ($plats as $p): ?>
            <div class="plat">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" class="img-plat">
                <h4><?= htmlspecialchars($p['nom']) ?></h4>
                
                <div class="box-grise texte-gauche">
                    <p class="m-0"><strong>🔥 Nutrition :</strong> <?= htmlspecialchars($p['nutrition'] ?? 'N/D') ?></p>
                    <p class="m-0 txt-alerte"><strong>⚠️ Allergènes :</strong> <?= (isset($p['allergenes']) && is_array($p['allergenes'])) ? htmlspecialchars(implode(', ', $p['allergenes'])) : 'Aucun' ?></p>
                </div>

                <p class="prix-plat"><?= number_format($p['prix'], 2) ?>€</p>
                
                <form action="traitement_panier.php" method="post" class="form-ajout">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id_article" value="<?= $p['id'] ?>">
                    <input type="hidden" name="nom_article" value="<?= htmlspecialchars($p['nom']) ?>">
                    <input type="hidden" name="prix" value="<?= $p['prix'] ?>">

                    <?php // Menu déroulant d'options personnalisables
                          // Génère la liste de choix si l'article contient des variantes configurables (ex: type de riz) ?>
                    <?php if (!empty($p['options_possibles'])): ?>
                        <select name="option_choisie" class="select-perso">
                            <option value="">-- Personnaliser --</option>
                            <?php foreach($p['options_possibles'] as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <div class="box-quantite">
                        <input type="number" name="quantite" value="1" min="1" class="input-qty">
                        <input type="submit" class="bouton-nav" value="Ajouter">
                    </div>
                </form> 
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
