<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: Connexion.php");
    exit();
}

// Par défaut, on utilise les infos de la personne connectée
$u = $_SESSION['user'];
$id_utilisateur_actuel = $u['id'];

// Si un ADMIN demande à voir un ID spécifique via l'URL (?id=U005)
if (isset($_GET['id']) && $_SESSION['user']['role'] === 'admin') {
    $id_cible = $_GET['id'];
    $fichier_users = 'data/utilisateurs.json';
    
    if (file_exists($fichier_users)) {
        $data_all = json_decode(file_get_contents($fichier_users), true);
        
        // On cherche l'utilisateur correspondant à l'ID cible dans le fichier
        foreach ($data_all['utilisateurs'] as $user_dossier) {
            if ($user_dossier['id'] === $id_cible) {
                // On remplace les infos d'affichage par celles de la cible
                $u = $user_dossier;
                $id_utilisateur_actuel = $id_cible;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Profil de <?= htmlspecialchars($u['informations']['nom']); ?></title>
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
                <p><strong>Rôle :</strong> <?= htmlspecialchars($u['role']); ?></p>
                <p><strong>Points Fidélité :</strong> <span class="points"><?= $u['points']; ?> pts</span></p>
            </div>
        </section>

        <section class="historique-commandes">
            <h2>Historique des Commandes</h2>
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
                        echo "<tr><td colspan='4'>Aucune commande enregistrée.</td></tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
