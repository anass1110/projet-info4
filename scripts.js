document.addEventListener("DOMContentLoaded", function() {
    
    // 1. GESTION DU COOKIE THÈME SOMBRE
    var btnTheme = document.getElementById('btn-theme');
    if (btnTheme) {
        btnTheme.addEventListener("click", function(e) {
            e.preventDefault();
            var isDark = document.body.classList.toggle('theme-sombre');
            document.cookie = "theme=" + (isDark ? "dark" : "light") + "; path=/; max-age=2592000";
        });
    }

    // 2. VALIDATION DU FORMULAIRE D'INSCRIPTION
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

    // 3. FETCH : ACTION CUISINE (Restaurateur)
    var btnCmd = document.querySelectorAll('.btn-action-cmd');
    btnCmd.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idCmd = this.getAttribute('data-id');
            var action = this.getAttribute('data-action');
            var carte = document.getElementById('cmd-' + idCmd);

            fetch('traitement_async_commandes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_commande=' + encodeURIComponent(idCmd) + '&action=' + encodeURIComponent(action)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    carte.querySelector('.statut-actuel').innerHTML = "<strong>Statut actuel :</strong> " + data.nouveau_statut;
                    btn.classList.add('cache');
                }
            });
        });
    });

    // 4. FETCH : ACTION LIVRAISON (Livreur)
    var btnLivraison = document.querySelectorAll('.btn-action-livreur');
    btnLivraison.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idCmd = this.getAttribute('data-id');
            var action = this.getAttribute('data-action');

            fetch('traitement_async_livraison.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_commande=' + encodeURIComponent(idCmd) + '&action=' + encodeURIComponent(action)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('actions-' + idCmd).innerHTML = "<p class='msg-succes'>Action enregistrée</p>";
                }
            });
        });
    });

    // 5. FETCH : ACCÈS ADMIN (Bloquer)
    var btnAdmin = document.querySelectorAll('.btn-action-admin');
    btnAdmin.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var idUser = this.getAttribute('data-id');

            fetch('traitement_async_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_user=' + encodeURIComponent(idUser) + '&action=bloquer'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    btn.innerHTML = "Bloqué";
                    btn.classList.add('etat-bloque');
                    btn.disabled = true;
                }
            });
        });
    });

    // 6. FETCH : AJOUT PANIER ASYNCHRONE (Délégation de clics)
    document.body.addEventListener('submit', function(e) {
        var form = e.target;
        if (form && form.getAttribute('action') === 'traitement_panier.php') {
            var actionInput = form.querySelector('input[name="action"]');
            if (actionInput && actionInput.value === 'ajouter') {
                e.preventDefault();
                var formData = new FormData(form);
                fetch('traitement_async_panier.php', {
                    method: 'POST',
                    body: formData
                })
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
        }
    });

    // 7. FETCH : RECHERCHE DYNAMIQUE LIVE
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
            });
        });
    }
});
