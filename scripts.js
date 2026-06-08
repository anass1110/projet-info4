document.addEventListener("DOMContentLoaded", function() {
    
    // Formulaire d'inscription
    // Intercepte la soumission pour vérifier la conformité des données utilisateur
    var formInsc = document.getElementById('form-inscription');
    if (formInsc) {
        formInsc.addEventListener('submit', function(event) {
            var mdp = document.getElementById('mdp').value;
            var msgErreur = document.getElementById('erreur-js');
            
            //  exige une longueur minimale pour le mot de passe avant envoi au serveur
            if (mdp.length < 6) {
                event.preventDefault(); // Annulation de l'envoi du formulaire pour bloquer l'inscription incorrecte
                msgErreur.innerHTML = "Le mot de passe doit faire au moins 6 caractères.";
                msgErreur.classList.remove('cache'); // Affichage du message d'erreur d'inscription à l'écran
            }
        });
    }

    // Formulaire de paiement sécurisé
    // Valide la conformité structurelle des informations bancaires saisies localement
    var formPaiement = document.getElementById('form-paiement');
    if (formPaiement) {
        formPaiement.addEventListener('submit', function(event) {
            var numCarte = document.getElementById('num_carte').value;
            var expCarte = document.getElementById('exp_carte').value;
            var cvcCarte = document.getElementById('cvc_carte').value;
            var zoneErreur = document.getElementById('erreur-bancaire-js');

            var regexNum = /^\d{16}$/; //  exactement 16 caractères numériques
            var regexExp = /^(0[1-9]|1[0-2])\/\d{2}$/; // Format MM/AA valide (mois de 01 à 12)
            var regexCvc = /^\d{3}$/; //  exactement 3 caractères numériques

            if (!regexNum.test(numCarte)) {
                event.preventDefault();
                zoneErreur.innerHTML = "⚠️ Numéro de carte invalide (16 chiffres requis).";
                zoneErreur.classList.remove('cache');
                return;
            }

            if (!regexExp.test(expCarte)) {
                event.preventDefault();
                zoneErreur.innerHTML = "⚠️ Format d'expiration invalide (MM/AA requis, ex: 12/28).";
                zoneErreur.classList.remove('cache');
                return;
            }

            if (!regexCvc.test(cvcCarte)) {
                event.preventDefault();
                zoneErreur.innerHTML = "⚠️ Code CVC invalide (3 chiffres requis au dos de la carte).";
                zoneErreur.classList.remove('cache');
                return;
            }
        });
    }

    // Gestion des compteurs de caractères en temps réel
    // Détecte les inputs limités et met à jour dynamiquement l'indicateur de saisie restante
    var indicateurs = document.querySelectorAll('.compteur-caracteres');
    indicateurs.forEach(function(span) {
        var idCible = span.getAttribute('data-cible');
        var inputCible = document.getElementById(idCible) || document.getElementsByName(idCible)[0];
        
        if (inputCible) {
            var limiteMax = parseInt(inputCible.getAttribute('maxlength'), 10);
            
            var actualiserCompteur = function() {
                var caracteresRestants = limiteMax - inputCible.value.length;
                span.textContent = caracteresRestants + " caractère(s) restant(s)";
            };

            inputCible.addEventListener('input', actualiserCompteur);
            actualiserCompteur(); // Calcul initial au chargement pour synchroniser l'affichage
        }
    });

    // Interactions du panier
    // Gestion asynchrone des actions d'ajout et de suppression d'articles
    function activerBoutonsPanier() {
        var formulairesPanier = document.querySelectorAll('form[action="traitement_panier.php"]');
        
        formulairesPanier.forEach(function(form) {
            //  empêche d'affecter plusieurs fois le même écouteur sur un formulaire déjà traité
            if (form.getAttribute('data-ecouteur-actif') !== 'oui') {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Bloque le rechargement de la page pour offrir une navigation fluide
                    var actionInput = form.querySelector('input[name="action"]');
                    
                    // Action d'ajout d'un produit (Maki, Sushi, Menu) dans la session de l'utilisateur
                    if (actionInput && actionInput.value === 'ajouter') {
                        fetch('traitement_async_panier.php', { method: 'POST', body: new FormData(form) })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) {
                                //  indique immédiatement au client que le plat a rejoint son panier
                                var btnSubmit = form.querySelector('input[type="submit"]');
                                var txtBackup = btnSubmit.value;
                                btnSubmit.value = "✓ Ajouté !";
                                btnSubmit.classList.add('etat-ajoute'); // Changement de couleur temporaire du bouton
                                setTimeout(function() {
                                    btnSubmit.value = txtBackup; // Restauration du texte d'origine après 2 secondes
                                    btnSubmit.classList.remove('etat-ajoute');
                                }, 2000);
                            }
                        });
                    }
                    
                    // Action de retrait d'un article depuis l'interface récapitative du panier
                    if (actionInput && actionInput.value === 'supprimer') {
                        fetch('traitement_async_panier.php', { method: 'POST', body: new FormData(form) })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            // Rechargement nécessaire pour recalculer les totaux de la page panier après suppression
                            if (data.success) { window.location.reload(); }
                        });
                    }
                });
                form.setAttribute('data-ecouteur-actif', 'oui'); // Marque le formulaire comme configuré
            }
        });
    }

    // Exécution globale au chargement pour lier les formulaires natifs de la page
    activerBoutonsPanier();

    // Recherche dynamique live
    // Filtrage en temps réel des produits du catalogue sans rafraîchir l'écran
    var champRecherche = document.getElementById('champ-recherche');
    var zoneCatalogue = document.getElementById('zone-catalogue');
    if (champRecherche && zoneCatalogue) {
        champRecherche.addEventListener('input', function() {
            var query = this.value; // Récupération de la chaîne de caractères saisie par le client
            fetch('traitement_async_recherche.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'recherche=' + encodeURIComponent(query)
            })
            .then(function(r) { return r.text(); }) // Attente d'un flux de réponse brut contenant le HTML des cartes filtrées
            .then(function(html) { 
                zoneCatalogue.innerHTML = html; // Injection immédiate des nouveaux plats correspondants dans la page
                //  rattache les écouteurs du panier sur les nouveaux éléments HTML injectés
                activerBoutonsPanier(); 
            });
        });
    }

    // Action cuisine
    // Pilotage des étapes de préparation des commandes par le restaurateur
    var boutonsCuisine = document.querySelectorAll('.btn-action-cmd');
    boutonsCuisine.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idCmd = this.getAttribute('data-id');
            var action = this.getAttribute('data-action');
            var carte = document.getElementById('cmd-' + idCmd); // Ciblage du bloc visuel de la commande concernée
            
            var textInitial = this.innerHTML;
            this.innerHTML = "⏳..."; this.disabled = true; // Désactivation du bouton pour éviter les requêtes doublons

            fetch('traitement_async_commandes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_commande=' + encodeURIComponent(idCmd) + '&action=' + encodeURIComponent(action)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    // Actualisation textuelle du libellé d'état sur l'affichage de la commande
                    carte.querySelector('.statut-actuel').innerHTML = "<strong>Statut actuel :</strong> <span style='color:green'>" + data.nouveau_statut + "</span>";
                    
                    var nomColonneCible = "";
                    //  Passage de la commande du statut "À préparer" à "En cours"
                    if (action === 'demarrer') {
                        btn.setAttribute('data-action', 'prete');
                        btn.innerHTML = "✅ Prête";
                        btn.classList.remove('btn-demarrer');
                        btn.classList.add('btn-prete');
                        btn.disabled = false;
                        nomColonneCible = "En Préparation";
                    } 
                    // Finalisation de la préparation, en attente de retrait ou de coursier
                    else if (action === 'prete') {
                        btn.style.display = 'none'; // Masquage permanent de l'action car la préparation est achevée
                        nomColonneCible = "En Attente";
                    }

                    // Déplacement physique de la carte dans la colonne correspondante du tableau de bord de la cuisine
                    var colonnes = document.querySelectorAll('.colonne-commandes');
                    colonnes.forEach(function(col) {
                        var titre = col.querySelector('h3');
                        if (titre && titre.textContent.trim() === nomColonneCible) {
                            col.appendChild(carte); // Transfert visuel direct de la commande vers sa nouvelle colonne
                            var msgVide = col.querySelector('.txt-vide');
                            if (msgVide) msgVide.style.display = 'none'; // Efface la mention "Aucune commande" de la colonne cible
                        }
                    });
                } else {
                    alert("Erreur Serveur.");
                    this.innerHTML = textInitial; this.disabled = false;
                }
            });
        });
    });
    
    // Interception de l'attribution d'un livreur sans rechargement de page 
    document.querySelectorAll('.form-attrib').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Bloque le rechargement natif de la page

            var formData = new FormData(form);

            fetch('traitement_async_commandes.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    // Récupère l'ID de la commande concernée
                    var idCommande = form.querySelector('input[name="id_commande"]').value;
                    var carteCommande = document.getElementById('cmd-' + idCommande);
                    
                    if (carteCommande) {
                        // Supprime proprement la carte de la colonne "En Attente"
                        // car elle passe instantanément au statut "En livraison"
                        carteCommande.remove();
                    }
                } else {
                    alert("Erreur lors de l'assignation du livreur : " + (data.message || "Inconnue"));
                }
            })
            .catch(function(error) { console.error('Erreur AJAX:', error); });
        });
    });

    // Action livreur
    // Validation finale ou remontée d'anomalies de livraison par le coursier
    var boutonsLivreur = document.querySelectorAll('.btn-action-livreur');
    boutonsLivreur.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idCmd = this.getAttribute('data-id');
            var action = this.getAttribute('data-action');
            var actionsDiv = document.getElementById('actions-' + idCmd); // Conteneur des choix d'actions de livraison
            
            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_livraison.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_commande=' + encodeURIComponent(idCmd) + '&action=' + encodeURIComponent(action)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    //  Remplace définitivement les boutons d'actions pour confirmer la clôture de la course
                    actionsDiv.innerHTML = "<p style='color:green; font-weight:bold; padding:15px; border:1px solid green; text-align:center;'>✅ Action enregistrée</p>";
                }
            });
        });
    });

    // Action admin
    // Suspension d'accès ou réactivation des comptes utilisateurs par le modérateur
    var boutonsAdmin = document.querySelectorAll('.btn-action-admin');
    boutonsAdmin.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idUser = this.getAttribute('data-id');
            var actionActuelle = this.getAttribute('data-action') || 'bloquer'; 

            this.innerHTML = "⏳..."; this.disabled = true;

            fetch('traitement_async_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_user=' + encodeURIComponent(idUser) + '&action=' + encodeURIComponent(actionActuelle)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if(data.success) {
                    // Inversion d'état (Toggle) : Met à jour dynamiquement l'action inverse disponible pour ce profil
                    if (actionActuelle === 'bloquer') {
                        btn.innerHTML = "Débloquer";
                        btn.setAttribute('data-action', 'debloquer');
                        btn.classList.add('etat-bloque'); // Application du style visuel restrictif gris
                    } else {
                        btn.innerHTML = "Bloquer";
                        btn.setAttribute('data-action', 'bloquer');
                        btn.classList.remove('etat-bloque'); // Restauration du style visuel actif rouge
                    }
                    btn.disabled = false;
                }
            });
        });
    });

    // Bascule du thème sombre
    // Gestionnaire du commutateur graphique jour/nuit appliqué globalement
    var btnTheme = document.getElementById('btn-toggle-theme');
    if (btnTheme) {
        btnTheme.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('theme-sombre'); // Commutation instantanée de la classe maîtresse sur le body
            
            //  Sauvegarde le choix du thème dans un cookie persistant (30 jours) pour les futurs chargements
            var themeActuel = document.body.classList.contains('theme-sombre') ? 'dark' : 'light';
            document.cookie = 'theme=' + themeActuel + '; path=/; max-age=2592000';
        });
    }

    // Changement du Plat Mystère sans recharger la page
    var btnChangerSurprise = document.getElementById('btn-changer-surprise');
