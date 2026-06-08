<?php
// Initialisation de la session
// Démarre la session si aucun flux de session n'est actif sur le serveur
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Contrôle de bannissement et expulsion immédiate
// Vérifie en temps réel le statut de l'utilisateur dans le référentiel JSON pour interdire l'accès aux comptes bloqués
if (isset($_SESSION['user'])) {
    $fichier_verif_users = 'data/utilisateurs.json';
    if (file_exists($fichier_verif_users)) {
        $data_verif = json_decode(file_get_contents($fichier_verif_users), true);
        $utilisateurs_liste = $data_verif['utilisateurs'] ?? [];
        
        foreach ($utilisateurs_liste as $user_verif) {
            if ($user_verif['id'] === $_SESSION['user']['id']) {
                if (isset($user_verif['statut']) && $user_verif['statut'] === 'bloque') {
                    // Destruction instantanée de la session courante
                    session_unset();
                    session_destroy();
                    // Redirection immédiate pour verrouiller l'usage du site
                    header("Location: Connexion.php?erreur=banni");
                    exit();
                }
                break;
            }
        }
    }
}

// Compteur du panier
// Calcule la somme cumulée des quantités d'articles stockées en session
$nb_articles_panier = 0;
if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $article) { 
        $nb_articles_panier += intval($article['quantite']); 
    }
}

// Configuration du thème graphique
// Récupère la préférence utilisateur stockée dans les cookies ou applique le mode clair par défaut
$cookie_theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<script>
    /* Traitement préventif du rendu visuel */
    /* Analyse le cookie de manière synchrone avant le rendu du body pour bloquer le flash blanc */
    if (document.cookie.indexOf("theme=dark") !== -1) {
        document.body.classList.add('theme-sombre');
    }
</script>
<header>
    <div id="logo"><h1>SUSHYTECH</h1></div>
    <nav>
        <ul>
            <li><a href="index.php" class="bouton-nav">Accueil</a></li>
            <li><a href="Produits.php" class="bouton-nav">Menu</a></li>
            <li>
                <a href="panier.php" class="bouton-nav btn-panier">
                    🛒 Panier 
                    <?php if ($nb_articles_panier > 0): ?>
                        <span class="nb-panier">(<?= $nb_articles_panier ?>)</span>
                    <?php endif; ?>
                </a>
            </li>
            
            <?php // Restriction d'affichage
                  // Alterne les liens de navigation selon l'état d'authentification de l'utilisateur ?>
            <?php if(!isset($_SESSION['user'])): ?>
                <li><a href="Connexion.php" class="bouton-nav">Connexion</a></li>
                <li><a href="Inscription.php" class="bouton-nav">S'inscrire</a></li>
            <?php else: ?>
                <li><a href="Profil.php" class="bouton-nav">Mon Profil</a></li>
                
                <?php // Contrôle des privilèges
                      // Affiche l'accès à l'espace d'administration ou de gestion selon le rôle stocké en session ?>
                <?php if($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="Administrateur.php" class="bouton-nav">Admin</a></li>
                <?php elseif($_SESSION['user']['role'] === 'restaurateur'): ?>
                    <li><a href="Commandes.php" class="bouton-nav">Commandes</a></li>
                <?php elseif($_SESSION['user']['role'] === 'livreur'): ?>
                    <li><a href="Livraison.php" class="bouton-nav">Livraisons</a></li>
                <?php endif; ?>
                
                <li><a href="Notation.php" class="bouton-nav">Notation</a></li>
                <li><a href="deconnexion.php" class="bouton-nav btn-deco">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <button id="btn-toggle-theme" class="bouton-nav btn-theme-top-right">
        🌓 Thème
    </button>
</header>
