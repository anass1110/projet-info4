<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // On récupère les saisies et on retire les espaces vides accidentels (trim)
    $login_saisi = trim($_POST['login-email'] ?? '');
    $mdp_saisi = trim($_POST['login-mdp'] ?? '');

    $fichier = 'data/utilisateurs.json';

    if (file_exists($fichier)) {
        $contenu = file_get_contents($fichier);
        $data = json_decode($contenu, true);

        // On vérifie si le JSON a bien été décodé et contient la clé 'utilisateurs'
        if ($data && isset($data['utilisateurs'])) {
            foreach ($data['utilisateurs'] as $index => $u) {
                // Comparaison stricte des identifiants (en clair pour la Phase 2)
                if ($u['login'] === $login_saisi && $u['mot_de_passe'] === $mdp_saisi) {
                    
                    // Mise à jour de la date (facultatif, mais on garde votre logique)
                    $data['utilisateurs'][$index]['dates']['derniere_connexion'] = date('Y-m-d H:i:s');
                    file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
                    
                    // Stockage de l'utilisateur en session
                    $_SESSION['user'] = $data['utilisateurs'][$index];
                    
                    // Redirection vers le profil
                    header("Location: Profil.php");
                    exit();
                }
            }
        }
    }
    
    // Si on arrive ici, c'est que ça a échoué
    header("Location: Connexion.php?erreur=1");
    exit();
}
?>