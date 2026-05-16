<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header("Location: accueil.php"); exit();
}

$fichier_json = 'data/commandes.json';
$commandes = [];
if (file_exists($fichier_json)) {
    $commandes = json_decode(file_get_contents($fichier_json), true)['commandes'] ?? [];
}

$livreurs = [];
$fichier_users = 'data/utilisateurs.json';
if (file_exists($fichier_users)) {
    foreach (json_decode(file_get_contents($fichier_users), true)['utilisateurs'] as $u) {
        if ($u['role'] === 'livreur') { $livreurs[] = $u; }
    }
}

$statuts = [ 'A preparer' => 'À Préparer', 'En cours' => 'En Préparation', 'En attente' => 'En Attente', 'En livraison' => 'En Livraison' ];
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
        <h2>Tableau de bord - Cuisine</h2>
        <div class="grille-statuts">
            <?php foreach ($statuts as $code_statut => $label_statut): ?>
                <div class="colonne-commandes">
                    <h3 class="colonne-titre"><?= $label_statut ?></h3>
                    
                    <?php 
                    $trouve = false;
                    foreach($commandes as $c): 
                        if($c['statut'] === $code_statut): 
                            $trouve = true;
                    ?>
                        <div class="carte-commande" id="cmd-<?= htmlspecialchars($c['id_commande']) ?>">
                            <p><strong>Commande :</strong> <?= htmlspecialchars($c['id_commande']) ?></p>
                            <p><strong>Type :</strong> <?= htmlspecialchars(ucfirst($c['type'])) ?></p>
                            <p><strong>Heure :</strong> <?= htmlspecialchars($c['heure_souhaitee']) ?></p>
                            
                            <div class="details-articles">
                                <ul class="liste-articles">
                                    <?php foreach($c['articles'] as $art): ?>
                                        <li><?= $art['quantite'] ?>x <?= htmlspecialchars($art['nom']) ?> <?= !empty($art['option']) ? "<i>(".htmlspecialchars($art['option']).")</i>" : "" ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <p class="statut-actuel"><strong>Statut actuel :</strong> <?= htmlspecialchars($c['statut']) ?></p>

                            <?php if($code_statut === 'A preparer'): ?>
                                <button class="bouton-nav btn-action-cmd btn-demarrer" data-id="<?= htmlspecialchars($c['id_commande']) ?>" data-action="demarrer">🔥 Commencer</button>
                            <?php elseif($code_statut === 'En cours'): ?>
                                <button class="bouton-nav btn-action-cmd btn-prete" data-id="<?= htmlspecialchars($c['id_commande']) ?>" data-action="prete">✅ Prête</button>
                            <?php elseif($code_statut === 'En attente'): ?>
                                <?php if($c['type'] === 'livraison'): ?>
                                    <form action="traitement_commande.php" method="post" class="form-attrib">
                                        <input type="hidden" name="action" value="attribuer_livreur">
                                        <input type="hidden" name="id_commande" value="<?= htmlspecialchars($c['id_commande']) ?>">
                                        <select name="id_livreur" required class="select-livreur">
                                            <option value="">-- Assigner un livreur --</option>
                                            <?php foreach($livreurs as $l): ?>
                                                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['informations']['nom'] . ' ' . $l['informations']['prenom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="submit" class="bouton-nav btn-expedier" value="Expédier">
                                    </form>
                                <?php else: ?>
                                    <span class="txt-attente">En attente du client</span>
                                <?php endif; ?>
                            <?php elseif($code_statut === 'En livraison'): ?>
                                <span class="txt-route">🚚 Coursier en route...</span>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif; 
                    endforeach; 
                    if(!$trouve): echo "<p class='txt-vide'>Aucune commande</p>"; endif;
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
 </script>
</html>
