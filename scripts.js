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

    // ========================================================================
    // PANIER : LA FONCTION LOURDE (Pour gérer l'asynchrone avec boucles classiques)
    // ========================================================================
    function activerBoutonsPanier() {
        var formulairesPanier = document.querySelectorAll('form[action="traitement_panier.php"]');
        
        formulairesPanier.forEach(function(form) {
            if (form.getAttribute('data-ecouteur-actif') !== 'oui') {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var actionInput = form.querySelector('input[name="action"]');
                    
                    // AJOUT
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
                    
                    // SUPPRESSION
                    if (actionInput && actionInput.value === 'supprimer') {
                        fetch('traitement_async_panier.php', { method: 'POST', body: new FormData(form) })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) { window.location.reload(); }
                        });
                    }
                });
                form.setAttribute('data-ecouteur-actif', 'oui');
            }
        });
    }

    // Lancement initial
    activerBoutonsPanier();

    // ========================================================================
    // RECHERCHE DYNAMIQUE LIVE
    // ========================================================================
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
                activerBoutonsPanier(); // On relance l'écoute sur les nouveaux plats
            });
        });
    }

    // ========================================================================
    // ACTION CUISINE (Avec déplacement de carte DOM)
    // ========================================================================
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
                    
                    var nomColonneCible = "";
                    if (action === 'demarrer') {
                        btn.setAttribute('data-action', 'prete');
                        btn.innerHTML = "✅ Prête";
                        btn.classList.remove('btn-demarrer');
                        btn.classList.add('btn-prete');
                        btn.disabled = false;
                        nomColonneCible = "En Préparation";
                    } else if (action === 'prete') {
                        btn.style.display = 'none';
                        nomColonneCible = "En Attente";
                    }

                    var colonnes = document.querySelectorAll('.colonne-commandes');
                    colonnes.forEach(function(col) {
                        var titre = col.querySelector('h3');
                        if (titre && titre.textContent.trim() === nomColonneCible) {
                            col.appendChild(carte); 
                            var msgVide = col.querySelector('.txt-vide');
                            if (msgVide) msgVide.style.display = 'none';
                        }
                    });
                } else {
                    alert("Erreur Serveur.");
                    this.innerHTML = textInitial; this.disabled = false;
                }
            });
        });
    });

    // ========================================================================
    // ACTION LIVREUR
    // ========================================================================
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

    // ========================================================================
    // ACTION ADMIN (Corrigée, sans erreur de syntaxe)
    // ========================================================================
    var boutonsAdmin = document.querySelectorAll('.btn-action-admin');
    boutonsAdmin.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idUser = this.getAttribute('data-id');
            // Correction : La variable est collée proprement
            var actionActuelle = this.getAttribute('data-action') || 'bloquer'; 

            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_user=' + encodeURIComponent(idUser) + '&action=' + encodeURIComponent(actionActuelle)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    // Logique de bascule (Toggle)
                    if (actionActuelle === 'bloquer') {
                        btn.innerHTML = "Débloquer";
                        btn.setAttribute('data-action', 'debloquer');
                        btn.classList.add('etat-bloque');
                    } else {
                        btn.innerHTML = "Bloquer";
                        btn.setAttribute('data-action', 'bloquer');
                        btn.classList.remove('etat-bloque');
                    }
                    btn.disabled = false;
                }
            });
        });
    });

});
