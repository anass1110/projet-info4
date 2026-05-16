<?php
$recherche = strtolower(trim($_POST['recherche'] ?? ''));
$donnees_menu = json_decode(file_get_contents('data/menu.json'), true);
$plats = $donnees_menu['plats'] ?? [];
$resultats = [];

foreach ($plats as $p) {
    if (empty($recherche) || strpos(strtolower($p['nom']), $recherche) !== false) {
        $resultats[] = $p;
    }
}

if (empty($resultats)) {
    echo "<p class='msg-aucun-resultat'>Aucun plat ne correspond à votre recherche.</p>";
} else {
    foreach ($resultats as $p) {
        ?>
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
        <?php
    }
}
?>
