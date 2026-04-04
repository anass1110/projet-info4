<?php
session_start();

// Redirection si l'utilisateur n'est pas connecté ou si le panier est vide
if (!isset($_SESSION['user']) || empty($_SESSION['panier'])) {
    header("Location: accueil.php");
    exit();
}

// Récupération du total envoyé depuis le panier ou défini dans le formulaire de paiement
$total = $_POST['total_commande'] ?? ($_POST['total_paye'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_carte'])) {
    // 1. Récupération des données du fichier JSON
    $fichier_cmd = 'data/commandes.json';
    
    // Lecture sécurisée du fichier (s'il n'existe pas encore, on crée l'arborescence de base)
    $contenu_fichier = file_exists($fichier_cmd) ? file_get_contents($fichier_cmd) : '{"commandes":[],"paiements":[]}';
    $data_cmd = json_decode($contenu_fichier, true);
    
    // Initialisation des tableaux s'ils n'existent pas encore
    if (!isset($data_cmd['commandes'])) $data_cmd['commandes'] = [];
    if (!isset($data_cmd['paiements'])) $data_cmd['paiements'] = [];
    
    // 2. Génération des identifiants et de la date de transaction [cite: 92]
    $id_commande = "CMD-" . date("Ymd") . "-" . rand(1000, 9999);
    $id_paiement = "PAY-" . time() . "-" . rand(100, 999);
    $id_client = $_SESSION['user']['id']; // Lien avec le client [cite: 91]
    $date_actuelle = date('Y-m-d H:i:s'); // Date de transaction [cite: 92]
    
    // 3. Masquage des coordonnées bancaires (on ne garde que les 4 derniers chiffres) [cite: 90]
    $num_carte_propre = str_replace(' ', '', $_POST['num_carte']);
    $carte_masquee = "**** **** **** " . substr($num_carte_propre, -4);
    
    // 4. Détermination de l'heure de préparation demandée
    $heure_choisie = ($_POST['timing'] === 'immediate') ? "Dès que possible" : $_POST['heure_souhaitee'];
    
    // 5. Création de la nouvelle commande
    $nouvelle_commande = [
        "id_commande" => $id_commande,
        "id_client" => $id_client,
        "client" => $_SESSION['user']['nom'] . " " . $_SESSION['user']['prenom'],
        "adresse" => $_SESSION['user']['adresse'] ?? "Sur place",
        "telephone" => $_SESSION['user']['telephone'] ?? "",
        "articles" => $_SESSION['panier'],
        "total" => floatval($_POST['total_paye']),
        "type" => $_POST['type_cmd'],
        "heure_souhaitee" => $heure_choisie,
        "date_commande" => $date_actuelle,
        "statut" => "A preparer", // Statut attendu pour la phase 2 côté restaurateur [cite: 102]
        "statut_paiement" => "Payé"
    ];

    // 6. Création de l'enregistrement du PAIEMENT
    $nouveau_paiement = [
        "id_paiement" => $id_paiement,
        "id_commande" => $id_commande,        // Lien explicite avec la commande [cite: 91]
        "id_client" => $id_client,            // Lien explicite avec le client [cite: 91]
        "montant" => floatval($_POST['total_paye']),
        "date_transaction" => $date_actuelle, // Date de la transaction bancaire [cite: 92]
        "coordonnees_bancaires" => $carte_masquee // Coordonnées protégées [cite: 90]
    ];

    // 7. Sauvegarde simultanée dans le fichier JSON
    $data_cmd['commandes'][] = $nouvelle_commande;
    $data_cmd['paiements'][] = $nouveau_paiement;
    file_put_contents($fichier_cmd, json_encode($data_cmd, JSON_PRETTY_PRINT));
    
    // 8. On vide le panier et on retire le coupon appliqué
    $_SESSION['panier'] = []; 
    unset($_SESSION['coupon']);
    
    // 9. Redirection finale vers le profil
    header("Location: Profil.php?succes_paiement=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - API CYBank</title>
    <link rel="stylesheet" type="text/css" href="fichier.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div id="contenu-formulaire" style="max-width: 500px; margin: 40px auto; padding: 20px;">
        <h2 style="text-align:center; color:#1C1C1C;">Passerelle Sécurisée - <span style="color:#BC002D;">CYBank</span></h2>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #ddd;">
            <p style="margin: 0;">Montant à régler : <strong style="font-size: 1.5em; color: #BC002D;"><?= number_format(floatval($total), 2) ?>€</strong></p>
        </div>
        
        <form action="paiement.php" method="post" autocomplete="off" style="display: flex; flex-direction: column; gap: 15px;">
            
            <input type="hidden" name="total_paye" value="<?= htmlspecialchars($total) ?>">
            <input type="hidden" name="type_cmd" value="<?= htmlspecialchars($_POST['type_commande'] ?? 'emporter') ?>">
            <input type="hidden" name="timing" value="<?= htmlspecialchars($_POST['timing'] ?? 'immediate') ?>">
            <input type="hidden" name="heure_souhaitee" value="<?= htmlspecialchars($_POST['heure_souhaitee'] ?? '') ?>">
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Numéro de Carte Bancaire :</label>
                <input type="text" name="num_carte" autocomplete="off" placeholder="0000 0000 0000 0000" required maxlength="19" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 1.1em; letter-spacing: 2px;">
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Expiration (MM/AA) :</label>
                    <input type="text" name="exp_carte" autocomplete="off" placeholder="12/26" required maxlength="5" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Cryptogramme (CVC) :</label>
                    <input type="text" name="cvc_carte" autocomplete="off" placeholder="123" required maxlength="3" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                </div>
            </div>
            
            <div class="actions-form" style="margin-top: 20px;">
                <input type="submit" class="bouton-nav" value="Confirmer et Payer" style="width: 100%; padding: 15px; font-size: 1.1em; background-color: #1C1C1C; color: white;">
            </div>
        </form>
        <p style="text-align: center; font-size: 0.8em; color: #666; margin-top: 15px;">🔒 Transaction cryptée de bout en bout</p>
    </div>
</body>
</html>