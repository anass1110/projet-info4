<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$nb_articles_panier = 0;
if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $article) { $nb_articles_panier += intval($article['quantite']); }
}
?>
<header>
    <div id="logo"><h1>SUSHYTECH</h1></div>
    <nav>
        <ul>
            <li><a href="accueil.php" class="bouton-nav">Accueil</a></li>
            <li><a href="Produits.php" class="bouton-nav">Menu</a></li>
            <li>
                <a href="panier.php" class="bouton-nav btn-panier">
                    🛒 Panier 
                    <?php if ($nb_articles_panier > 0): ?>
                        <span class="nb-panier">(<?= $nb_articles_panier ?>)</span>
                    <?php endif; ?>
                </a>
            </li>
            <li><button id="btn-theme" class="bouton-nav">Thème</button></li>
            
            <?php if(!isset($_SESSION['user'])): ?>
                <li><a href="Connexion.php" class="bouton-nav">Connexion</a></li>
                <li><a href="Inscription.php" class="bouton-nav">S'inscrire</a></li>
            <?php else: ?>
                <li><a href="Profil.php" class="bouton-nav">Mon Profil</a></li>
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
</header>
<script src="scripts.js"></script>
