<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: Connexion.php");
    exit();
}
$u = $_SESSION['user'];
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
            <h2>Profil de <?php echo htmlspecialchars($u['informations']['prenom'] . ' ' . $u['informations']['nom']); ?></h2>
            <div class="form-style">
                <p><strong>Login :</strong> <?php echo htmlspecialchars($u['login']); ?></p>
                <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($u['informations']['pseudo']); ?></p>
                <p><strong>Date de naissance :</strong> <?php echo htmlspecialchars($u['informations']['naissance']); ?></p>
                <p><strong>Inscription :</strong> <?php echo htmlspecialchars($u['dates']['inscription']); ?></p>
                <p><strong>Points Fidélité :</strong> <span class="points"><?php echo $u['points']; ?> pts</span></p>
            </div>
        </section>

        <section class="historique-commandes">
            <h2>Historique</h2>
            <table>
                <thead>
                    <tr><th>Date</th><th>Statut</th><th>Total</th></tr>
                </thead>
                <tbody>
                <?php 
                $fichier_cmd = 'data/commandes.json';
                if (file_exists($fichier_cmd)) {
                    $cmds = json_decode(file_get_contents($fichier_cmd), true)['commandes'];
                    $nom_complet = $u['informations']['nom'] . " " . $u['informations']['prenom'];
                    foreach ($cmds as $c) {
                        if ($c['client'] === $nom_complet) {
                            echo "<tr>
                                    <td>".htmlspecialchars($c['heure_souhaitee'])."</td>
                                    <td>".htmlspecialchars($c['statut'])."</td>
                                    <td>".number_format($c['total'], 2)."€</td>
                                  </tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='3'>Aucune commande.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>