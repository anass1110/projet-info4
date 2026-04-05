<?php
// Initialisation de la session
session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['user_login'] ?? '';
    $mdp = $_POST['user_password'] ?? '';
    
    if(empty($login) || empty($mdp)) {
        header("Location: Inscription.php?erreur=1");
        exit(); 
    }

    $fichier = 'data/utilisateurs.json';
    
    if(!file_exists($fichier)) { 
        file_put_contents($fichier, json_encode(["utilisateurs" => []]));
    }
    
    $data = json_decode(file_get_contents($fichier), true); 
    
    // Parcours séquentiel des utilisateurs pour prévenir la duplication des identifiants 
    foreach ($data['utilisateurs'] as $u) {
        if ($u['login'] === $login) {
            // Interruption du flux et redirection avec code d'erreur spécifique
            header("Location: Inscription.php?erreur=doublon");
            exit(); 
        }
    }
    
    // Construction de la structure de données du nouvel utilisateur 
    $nouvel_user = [
        "id" => "U" . str_pad(count($data['utilisateurs']) + 1, 3, "0", STR_PAD_LEFT),
        "login" => $login,
        "mot_de_passe" => $mdp,
        "role" => "client",
        "informations" => [
            "nom" => $_POST['user_nom'],
            "prenom" => $_POST['user_prenom'],
            "pseudo" => $_POST['user_pseudo'] ?? '',
            "naissance" => $_POST['user_naissance'] ?? '',
            "adresse" => $_POST['user_adresse'] ?? '',
            "telephone" => $_POST['user_tel'] ?? ''
        ],
        "dates" => [
            "inscription" => date('Y-m-d H:i:s'),
            "derniere_connexion" => "Jamais"
        ],
        "points" => 0
    ];

    $data['utilisateurs'][] = $nouvel_user;
    file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT)); 

    header("Location: Connexion.php?succes=inscription"); 
    exit();
}
?>
