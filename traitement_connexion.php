<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login-email']; // On garde le nom du champ HTML
    $mdp = $_POST['login-mdp'];

    $fichier = 'data/utilisateurs.json';
    if(file_exists($fichier)) {
        $data = json_decode(file_get_contents($fichier), true);
        
        foreach ($data['utilisateurs'] as $index => $u) {
            if ($u['login'] === $login && $u['mot_de_passe'] === $mdp) {
                
                // Mise à jour de la date de dernière connexion
                $data['utilisateurs'][$index]['dates']['derniere_connexion'] = date('Y-m-d H:i:s');
                file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
                
                // On met l'utilisateur à jour dans la session
                $_SESSION['user'] = $data['utilisateurs'][$index];
                header("Location: Profil.php");
                exit();
            }
        }
    }
    header("Location: Connexion.php?erreur=1");
    exit();
}
?>