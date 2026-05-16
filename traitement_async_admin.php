<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["success" => false]); exit();
}

// On vérifie que l'ID et l'action (bloquer ou debloquer) sont présents
if (isset($_POST['id_user']) && isset($_POST['action'])) {
    $fichier = 'data/utilisateurs.json';
    $data = json_decode(file_get_contents($fichier), true);
    
    foreach ($data['utilisateurs'] as $i => $u) {
        if ($u['id'] == $_POST['id_user']) {
            if ($_POST['action'] === 'bloquer') {
                $data['utilisateurs'][$i]['statut'] = 'bloque';
            } elseif ($_POST['action'] === 'debloquer') {
                // On retire le statut de blocage
                $data['utilisateurs'][$i]['statut'] = 'actif'; 
            }
            
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["success" => true]);
            exit();
        }
    }
}
echo json_encode(["success" => false]);
?>
