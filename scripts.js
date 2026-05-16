document.addEventListener("DOMContentLoaded", function() {
    
    // 1. VALIDATION DU FORMULAIRE D'INSCRIPTION
    var formInsc = document.getElementById('form-inscription');
    if (formInsc) {
        formInsc.addEventListener('submit', function(event) {
            var mdp = document.getElementById('mdp').value;
            var msgErreur = document.getElementById('erreur-js');
            if (mdp.length < 6) {
                event.preventDefault();
                msgErreur.innerHTML = "Le mot de passe doit faire au moins 6 caractères.";
                msgErreur.classList.remove('cache');
            }
        });
    }

  
    function activerBoutonsPanier() {
        var formulairesPanier = document.querySelectorAll('form[action="traitement_panier.php"]');
        
        formulairesPanier.forEach(function(form) {
            // On vérifie si on n'a pas déjà mis un écouteur (pour éviter que l'action s'exécute 2 fois)
            if (form.getAttribute('data-ecouteur-actif') !== 'oui') {
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var actionInput = form.querySelector('input[name="action"]');
                    
                    // AJOUT PANIER
                    if (actionInput && actionInput.value === 'ajouter') {
                        fetch('traitement_async_panier.php', { method: 'POST', body: new FormData(form) })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) {
                                var btnSubmit = form.querySelector('input[type="submit"]');
                                var txtBackup = btnSubmit.value;
                                btnSubmit.value = "✓ Ajouté !";
                                btnSubmit.classList.add('etat-ajoute');
                                setTimeout(function() {
                                    btnSubmit.value = txtBackup;
                                    btnSubmit.classList.remove('etat-ajoute');
                                }, 2000);
                            }
                        });
                    }
                    
                    // SUPPRESSION PANIER
                    if (actionInput && actionInput.value === 'supprimer') {
                        fetch('traitement_async_panier.php', { method: 'POST', body: new FormData(form) })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) { window.location.reload(); }
                        });
                    }
                });
                
                // On marque le formulaire pour dire qu'il est "surveillé"
                form.setAttribute('data-ecouteur-actif', 'oui');
            }
        });
    }

    // On lance la fonction une première fois au chargement normal de la page
    activerBoutonsPanier();



    // RECHERCHE DYNAMIQUE LIVE

    var champRecherche = document.getElementById('champ-recherche');
    var zoneCatalogue = document.getElementById('zone-catalogue');
    if (champRecherche && zoneCatalogue) {
        champRecherche.addEventListener('input', function() {
            var query = this.value;
            fetch('traitement_async_recherche.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'recherche=' + encodeURIComponent(query)
            })
            .then(function(r) { return r.text(); })
            .then(function(html) { 
                zoneCatalogue.innerHTML = html; 
                
             
                // Les nouveaux plats viennent d'apparaître, on doit relancer la boucle sur leurs boutons
                activerBoutonsPanier(); 
            });
        });
    }


    // BOUCLES CLASSIQUES (CUISINE, LIVREUR, ADMIN) - Statiques au chargement

    
   
   // ACTION CUISINE
    var boutonsCuisine = document.querySelectorAll('.btn-action-cmd');
    boutonsCuisine.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idCmd = this.getAttribute('data-id');
            var action = this.getAttribute('data-action');
            var carte = document.getElementById('cmd-' + idCmd);
            
            var textInitial = this.innerHTML;
            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_commandes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_commande=' + encodeURIComponent(idCmd) + '&action=' + encodeURIComponent(action)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    //  On met à jour le texte du statut en vert
                    carte.querySelector('.statut-actuel').innerHTML = "<strong>Statut actuel :</strong> <span style='color:green'>" + data.nouveau_statut + "</span>";
                    
                    //  TRANSFORMATION DU BOUTON ET PRÉPARATION DU DÉPLACEMENT
                    var nomColonneCible = "";
                    
                    if (action === 'demarrer') {
                        btn.setAttribute('data-action', 'prete');
                        btn.innerHTML = "✅ Prête";
                        btn.classList.remove('btn-demarrer');
                        btn.classList.add('btn-prete');
                        btn.disabled = false;
                        nomColonneCible = "En Préparation"; // Nom exact du <h3> dans ton PHP
                    } else if (action === 'prete') {
                        btn.style.display = 'none'; // Le bouton disparaît
                        nomColonneCible = "En Attente"; // Nom exact du <h3> dans ton PHP
                    }

                    //  LE DÉPLACEMENT PHYSIQUE DANS LE DOM 
                    var colonnes = document.querySelectorAll('.colonne-commandes');
                    colonnes.forEach(function(col) {
                        var titre = col.querySelector('h3');
                        if (titre && titre.textContent.trim() === nomColonneCible) {
                            // On arrache la carte pour la coller dans la nouvelle colonne
                            col.appendChild(carte); 
                            
                            // Si la colonne cible avait le texte "Aucune commande", on le cache
                            var msgVide = col.querySelector('.txt-vide');
                            if (msgVide) msgVide.style.display = 'none';
                        }
                    });
                }
            });
        });
    });
    // ACTION LIVREUR
    var boutonsLivreur = document.querySelectorAll('.btn-action-livreur');
    boutonsLivreur.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idCmd = this.getAttribute('data-id');
            var action = this.getAttribute('data-action');
            var actionsDiv = document.getElementById('actions-' + idCmd);
            
            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_livraison.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_commande=' + encodeURIComponent(idCmd) + '&action=' + encodeURIComponent(action)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    actionsDiv.innerHTML = "<p style='color:green; font-weight:bold; padding:15px; border:1px solid green; text-align:center;'>✅ Action enregistrée</p>";
                }
            });
        });
    });

// ACTION ADMIN 
    var boutonsAdmin = document.querySelectorAll('.btn-action-admin');
    boutonsAdmin.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idUser = this.getAttribute('data-id');
            // On récupère l'action actuelle du bouton (bloquer ou debloquer)
            var action Actuelle = this.getAttribute('data-action') || 'bloquer'; 

            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_user=' + encodeURIComponent(idUser) + '&action=' + encodeURIComponent(actionActuelle)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    if (actionActuelle === 'bloquer') {
                        // L'utilisateur est bloqué, le bouton devient l'outil de déblocage
                        btn.innerHTML = "Débloquer";
                        btn.setAttribute('data-action', 'debloquer');
                        btn.classList.add('etat-bloque');
                    } else {
                        // L'utilisateur est débloqué, le bouton redevient l'outil de blocage
                        btn.innerHTML = "Bloquer";
                        btn.setAttribute('data-action', 'bloquer');
                        btn.classList.remove('etat-bloque');
                    }
                    btn.disabled = false;
                }
            });
        });
    });
