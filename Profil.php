<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: Connexion.php");
    exit();
}

// Initialisation par défaut sur le profil de l'utilisateur authentifié
$id_utilisateur_actuel = $_SESSION['user']['id'];
$u = $_SESSION['user'];

// Surcharge de l'identifiant cible si l'utilisateur possède le rôle d'administration
if (isset($_GET['id']) && $_SESSION['user']['role'] === 'admin') {
    $id_utilisateur_actuel = $_GET['id'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Mon Profil</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-profil">
        <section class="infos-perso">
            <h2>Profil de <?= htmlspecialchars($u['informations']['prenom'] . ' ' . $u['informations']['nom']); ?></h2>
            <div class="form-style">
                <p><strong>ID Client :</strong> <?= htmlspecialchars($u['id']); ?></p>
                <p><strong>Login :</strong> <?= htmlspecialchars($u['login']); ?></p>
                <p><strong>Points Fidélité :</strong> <span class="points"><?= $u['points']; ?> pts</span></p>
            </div>
        </section>

        <section class="historique-commandes">
            <h2>Mon Historique de Commandes</h2>
            <table>
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $fichier_cmd = 'data/commandes.json';
                if (file_exists($fichier_cmd)) {
                    $json_data = json_decode(file_get_contents($fichier_cmd), true);
                    $cmds = $json_data['commandes'] ?? [];
                    
                    $nb_trouve = 0;
                    foreach ($cmds as $c) {
                        // comparaison par id unique 
                        if (isset($c['id_client']) && $c['id_client'] === $id_utilisateur_actuel) {
                            $nb_trouve++;
                            echo "<tr>
                                    <td><strong>".htmlspecialchars($c['id_commande'])."</strong></td>
                                    <td>".htmlspecialchars($c['date_commande'])."</td>
                                    <td><span class='statut-tag'>".htmlspecialchars($c['statut'])."</span></td>
                                    <td>".number_format($c['total'], 2)."€</td>
                                  </tr>";
                        }
                    }
                    if ($nb_trouve === 0) {
                        echo "<tr><td colspan='4'>Aucune commande trouvée pour votre compte.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Fichier de données introuvable.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
