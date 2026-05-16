<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["success" => false]); exit();
}

if (isset($_POST['id_user']) && $_POST['action'] === 'bloquer') {
    $fichier = 'data/utilisateurs.json';
    $data = json_decode(file_get_contents($fichier), true);
    
    foreach ($data['utilisateurs'] as $i => $u) {
        //  Double égal (==) 
        if ($u['id'] == $_POST['id_user']) {
            $data['utilisateurs'][$i]['statut'] = 'bloque';
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true]);
            exit();
        }
    }
}
echo json_encode(["success" => false]);
?>
