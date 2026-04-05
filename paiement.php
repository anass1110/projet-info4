<?php
session_start();

// 1. Sécurité : Redirection si l'utilisateur n'est pas connecté ou si le panier est vide
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

// Récupération du total (soit via le bouton du panier, soit via la validation interne)
$total = $_POST['total_commande'] ?? ($_POST['total_paye'] ?? 0);

// --- LOGIQUE DE TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_carte'])) {
    
    $fichier_cmd = 'data/commandes.json';
    
    // Lecture du fichier JSON existant
    $contenu_fichier = file_exists($fichier_cmd) ? file_get_contents($fichier_cmd) : '{"commandes":[],"paiements":[]}';
    $data_cmd = json_decode($contenu_fichier, true);
    
    if (!isset($data_cmd['commandes'])) $data_cmd['commandes'] = [];
    if (!isset($data_cmd['paiements'])) $data_cmd['paiements'] = [];

    // Génération d'un ID de commande unique pour la Phase 2
    $id_commande = "CMD-" . date("Ymd") . "-" . rand(1000, 9999);

    // --- CORRECTION MAJEURE : L'ID CLIENT ---
    // On utilise $_SESSION['user']['id'] pour garantir l'unicité
    $nouvelle_commande = [
        "id_commande"     => $id_commande,
        "id_client"       => $_SESSION['user']['id'], 
        "client"          => $_SESSION['user']['informations']['nom'] . " " . $_SESSION['user']['informations']['prenom'],
        "adresse"         => ($_POST['type_commande'] === 'livraison') ? $_SESSION['user']['informations']['adresse'] : "À emporter / Sur place",
        "telephone"       => $_SESSION['user']['informations']['telephone'],
        "articles"        => $_SESSION['panier'],
        "total"           => floatval($total),
        "type"            => $_POST['type_commande'],
        "heure_souhaitee" => !empty($_POST['heure_souhaitee']) ? $_POST['heure_souhaitee'] : "Dès que possible",
        "date_commande"   => date("Y-m-d H:i:s"),
        "statut"          => "A preparer",
        "statut_paiement" => "Payé"
    ];

    // Simulation de l'enregistrement du paiement (Masquage de la carte)
    $num_masque = "XXXX-XXXX-XXXX-" . substr($_POST['num_carte'], -4);
    $nouveau_paiement = [
        "id_commande" => $id_commande,
        "id_client"   => $_SESSION['user']['id'],
        "montant"     => floatval($total),
        "carte_fin"   => $num_masque,
        "date"        => date("Y-m-d H:i:s")
    ];

    // Ajout aux données
    $data_cmd['commandes'][] = $nouvelle_commande;
    $data_cmd['paiements'][] = $nouveau_paiement;

    // Sauvegarde physique dans le fichier JSON
    file_put_contents($fichier_cmd, json_encode($data_cmd, JSON_PRETTY_PRINT));

    // Vider le panier après succès
    $_SESSION['panier'] = [];

    // Redirection vers le profil avec un message de succès
    header("Location: Profil.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SushyTech - Paiement Sécurisé</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div id="contenu-formulaire">
        <div style="background: white; padding: 30px; border-radius: 15px; border: 2px solid #1C1C1C; max-width: 500px; margin: 40px auto;">
            <h2 style="text-align: center; margin-top: 0;">💳 Finaliser ma commande</h2>
            
            <p style="text-align: center; font-size: 1.2em;">Total à régler : <strong><?= number_format($total, 2) ?> €</strong></p>
            <hr>

            <form action="paiement.php" method="post">
                <input type="hidden" name="total_paye" value="<?= $total ?>">
                <input type="hidden" name="type_commande" value="<?= htmlspecialchars($_POST['type_commande'] ?? 'emporter') ?>">
                <input type="hidden" name="heure_souhaitee" value="<?= htmlspecialchars($_POST['heure_souhaitee'] ?? '') ?>">

                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom sur la carte :</label>
                <input type="text" name="nom_carte" placeholder="M. JEAN DUPONT" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;">

                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Numéro de carte (16 chiffres) :</label>
                <input type="text" name="num_carte" pattern="\d{16}" title="16 chiffres requis" placeholder="1234567812345678" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; letter-spacing: 2px;">

                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="font-weight: bold;">Expiration :</label>
                        <input type="text" name="exp" placeholder="MM/AA" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: bold;">CVC :</label>
                        <input type="text" name="cvc" placeholder="123" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                </div>

                <div style="text-align: center;">
                    <input type="submit" value="Confirmer le paiement" class="bouton-nav" style="width: 100%; padding: 15px; background-color: #BC002D; color: white; border: none; cursor: pointer;">
                    <p style="font-size: 0.8em; color: gray; margin-top: 10px;">🔒 Paiement sécurisé via CYBank Gateway</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>