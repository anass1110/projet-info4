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
                    carte.querySelector('.statut-actuel').innerHTML = "<strong>Statut actuel :</strong> <span style='color:green'>" + data.nouveau_statut + "</span>";
                    btn.style.display = 'none';
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
            
            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_user=' + encodeURIComponent(idUser) + '&action=bloquer'
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    btn.innerHTML = "Bloqué";
                    btn.classList.add('etat-bloque');
                }
            });
        });
    });

});
