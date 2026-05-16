<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header("Location: accueil.php"); exit(); }
$fichier_json = 'data/utilisateurs.json';
$utilisateurs = file_exists($fichier_json) ? json_decode(file_get_contents($fichier_json), true)['utilisateurs'] : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Administration</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="contenu-admin">
        <h2>Gestion des Utilisateurs</h2>
        <table class="table-admin">
            <thead>
                <tr><th>ID</th><th>Nom & Prénom</th><th>Rôle</th><th>Actions</th></tr>
            </thead>
            <tbody>
                 <?php foreach ($utilisateurs as $u): ?>
                    <tr class="user-row">
                         <td><?= htmlspecialchars($u['id']) ?></td>
                         <td><?= htmlspecialchars($u['informations']['nom'] . ' ' . $u['informations']['prenom']) ?></td>
                         <td><?= htmlspecialchars($u['role']) ?></td>
                         <td>
                             <?php 
                             //  on vérifie la base de données avant d'afficher le bouton
                             $estBloque = (isset($u['statut']) && $u['statut'] === 'bloque');
                             if ($estBloque): 
                             ?>
                                 <button class="bouton-nav btn-action-admin etat-bloque" data-id="<?= htmlspecialchars($u['id']) ?>" data-action="debloquer">Débloquer</button>
                             <?php else: ?>
                                 <button class="bouton-nav btn-action-admin btn-bloquer" data-id="<?= htmlspecialchars($u['id']) ?>" data-action="bloquer">Bloquer</button>
                             <?php endif; ?>
                             
                             <select class="bouton-nav"><option value="standard">Standard</option><option value="vip">VIP</option></select>
                             <a href="Profil.php?id=<?= urlencode($u['id']) ?>" class="bouton-nav">Voir Profil</a>
                         </td> 
                    </tr> 
                 <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
