<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    echo json_encode(["success" => false]); exit();
}
if (isset($_POST['id_commande']) && isset($_POST['action'])) {
    $fichier = 'data/commandes.json';
    $data = json_decode(file_get_contents($fichier), true);
    foreach ($data['commandes'] as $i => $c) {
        if ($c['id_commande'] === $_POST['id_commande']) {
            if ($_POST['action'] === 'valider') {
                $data['commandes'][$i]['statut'] = 'Livrée';
            } elseif ($_POST['action'] === 'abandonner') {
                $data['commandes'][$i]['statut'] = 'Erreur Livraison';
            }
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true]);
            exit();
        }
    }
}
echo json_encode(["success" => false]);
?>
