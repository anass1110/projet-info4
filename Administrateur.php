<?php
// Vérification des droits d'accès (Contrôle de rôle)
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: accueil.php");
    exit();
}

// Extraction du référentiel des utilisateurs
$fichier_json = 'data/utilisateurs.json';
$utilisateurs = [];
if(file_exists($fichier_json)){
    $utilisateurs = json_decode(file_get_contents($fichier_json), true)['utilisateurs'];
}
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
                <tr>
                    <th>ID</th>
                    <th>Nom & Prénom</th>
                    <th>Rôle</th>
                    <th>Actions (Affichage Phase 2)</th>
                </tr>
            </thead>
            <tbody>
                 <?php foreach ($utilisateurs as $u): ?>
                    <tr class="user-row">
                         <td><?= htmlspecialchars($u['id']) ?></td>
                         <td><?= htmlspecialchars($u['informations']['nom'] . ' ' . $u['informations']['prenom']) ?></td>
                         <td><?= htmlspecialchars($u['role']) ?></td>
                
                         <td>
                             <button class="bouton-nav" style="color: red; border-color: red;">Bloquer</button>
                             <select class="bouton-nav">
                                 <option value="standard">Standard</option>
                                 <option value="vip">VIP</option>
                             </select>
                             <a href="Profil.php?id=<?= urlencode($u['id']) ?>" class="bouton-nav">Voir Profil</a>
                         </td> 
                    </tr> 
                 <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
