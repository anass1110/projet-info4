<?php
session_start();

// Protection : Seul le restaurateur peut manipuler les statuts de cuisson
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande']) && isset($_POST['action'])) {
    $id_cmd = $_POST['id_commande'];
    $action = $_POST['action'];
    $fichier = 'data/commandes.json';

    if (file_exists($fichier)) {
        $data = json_decode(file_get_contents($fichier), true);
        $mis_a_jour = false;

        foreach ($data['commandes'] as $index => $c) {
            if ($c['id_commande'] === $id_cmd) {
                
                if ($action === 'demarrer') {
                    // Passe de 'En attente' à 'En cours' 
                    $data['commandes'][$index]['statut'] = 'En cours';
                    $mis_a_jour = true;
                } 
                elseif ($action === 'prete') {
                    // Passe de 'En cours' à 'Prête' 
                    $data['commandes'][$index]['statut'] = 'Prête';
                    $mis_a_jour = true;
                }
                elseif ($action === 'attribuer_livreur' && isset($_POST['id_livreur'])) {
                    //  Attribue le livreur sans rechargement
                    $data['commandes'][$index]['id_livreur'] = $_POST['id_livreur'];
                    $data['commandes'][$index]['statut'] = 'En livraison';
                    $mis_a_jour = true;
                }
                break;
            }
        }

        if ($mis_a_jour) {
            file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true, 'nouveau_statut' => $data['commandes'][$index]['statut']]);
            exit();
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Erreur de traitement']);
exit();
?>
