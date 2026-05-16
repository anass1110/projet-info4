<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $trouve = false;
    for ($i = 0; $i < count($_SESSION['panier']); $i++) {
        if ($_SESSION['panier'][$i]['id'] === $_POST['id_article']) {
            $_SESSION['panier'][$i]['quantite'] += intval($_POST['quantite']);
            $trouve = true; break; 
        }
    }
    if (!$trouve) {
        $_SESSION['panier'][] = [
            'id' => $_POST['id_article'],
            'nom' => $_POST['nom_article'],
            'prix' => floatval($_POST['prix']),
            'quantite' => intval($_POST['quantite']),
            'option' => $_POST['option_choisie'] ?? ''
        ];
    }
    echo json_encode(["success" => true]);
    exit();
}
echo json_encode(["success" => false]);
?>
