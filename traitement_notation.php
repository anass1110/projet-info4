<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
    $fichier = 'data/avis.json';
    
    // Création de l'architecture JSON racine si nécessaire
    if(!file_exists($fichier)) {
        file_put_contents($fichier, json_encode(["avis" => []]));
    }
    
    $data = json_decode(file_get_contents($fichier), true); 
    
    // Nettoyage et typage strict des données entrantes avant persistance
    $nouvel_avis = [
        "id_client" => $_SESSION['user']['id'],
        "note_produit" => intval($_POST['note_produit']),
        "note_livraison" => intval($_POST['note_livraison']),
        "commentaire" => trim($_POST['user_commentaire']), 
        "date" => date('Y-m-d H:i:s')
    ];
    
    $data['avis'][] = $nouvel_avis;
    file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT)); 
    
    header("Location: accueil.php"); 
    exit();
}
?>
