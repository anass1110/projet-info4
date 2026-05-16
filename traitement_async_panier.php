<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // CAS 1 : AJOUTER
    if ($_POST['action'] === 'ajouter') {
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
        echo json_encode(["success" => true, "action" => "ajouter"]); exit();
    }

    // CAS 2 : SUPPRIMER
    if ($_POST['action'] === 'supprimer') {
        if (isset($_POST['index_article'])) {
            $index = intval($_POST['index_article']);
            if (isset($_SESSION['panier'][$index])) {
                array_splice($_SESSION['panier'], $index, 1);
            }
        }
        echo json_encode(["success" => true, "action" => "supprimer"]); exit();
    }
}
echo json_encode(["success" => false]);
?>
