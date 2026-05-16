<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    echo json_encode(["success" => false]); exit();
}

if (isset($_POST['id_commande']) && isset($_POST['action'])) {
    $fichier = 'data/commandes.json';
    $data = json_decode(file_get_contents($fichier), true);
    
    foreach ($data['commandes'] as $i => $c) {
        // Double égal (==) au lieu du triple (===)
        if ($c['id_commande'] == $_POST['id_commande']) {
            if ($_POST['action'] === 'demarrer') {
                $data['commandes'][$i]['statut'] = 'En cours';
                $nouveau_statut = 'En cours';
            } elseif ($_POST['action'] === 'prete') {
                $data['commandes'][$i]['statut'] = 'En attente';
                $nouveau_statut = 'En attente';
            }
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true, "nouveau_statut" => $nouveau_statut]);
            exit();
        }
    }
}
echo json_encode(["success" => false]);
?>
