/* ============================================================
   BIBLIOTHEQUE NUMERIQUE — app.js
   JavaScript ES6+ (aucune dépendance, éco-conception) :
   - Menu mobile
   - Recherche AJAX temps réel (debounce + loader + suggestions)
   - Filtres rapides par carte (client-side)
   - Toasts (flash) avec auto-fermeture
   - Modale de confirmation (remplace window.confirm)
   - Panneau latéral (slide-over) ajout/édition admin
   - Micro-interactions (saisie note 5 étoiles, boutons en cours, apparition au scroll)
   Accessibilité : focus, Escape, aria, prefers-reduced-motion.
   ============================================================ */

(() => {
    'use strict';

    const PL = window.PL = window.PL || {};

    const REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const BASE_URL = (document.body && document.body.dataset.appBase) || '';

    /** Construit l'URL d'un point d'entrée du front controller. */
    const appUrl = (page, query) =>
        BASE_URL + '/index.php?page=' + encodeURIComponent(page) + (query ? '&' + query : '');

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    /* ----------------------------------------------------------
       1. Menu mobile
       ---------------------------------------------------------- */
    function initNavToggle() {
        const toggle = $('.nav-toggle');
        const nav = $('#nav-principale');
        if (!toggle || !nav) return;

        const close = () => {
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            const isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });

        // Ferme le menu après un clic sur un lien (mobile).
        $$('a', nav).forEach((link) => link.addEventListener('click', close));
    }

    /* ----------------------------------------------------------
       2. Recherche AJAX + filtres rapides
       ---------------------------------------------------------- */
    function initSearch() {
        const form = $('[data-search-form]');
        const input = $('[data-search-input]');
        const results = $('[data-search-results]');
        if (!input || !results) return;

        const chips = $$('[data-filter]');
        const grid = $('[data-book-grid]');
        const cards = grid ? $$('[data-book-card]', grid) : [];
        const count = $('[data-result-count]');
        const originalCount = count ? count.textContent.trim() : '';
        const FIELDS = { titre: 'title', auteur: 'author', edition: 'edition' };

        let debounce = null;
        let activeChip = 'all';

        /* -- Suggestions AJAX -- */
        async function fetchSuggestions(term) {
            // État "chargement" (loader).
            results.hidden = false;
            results.innerHTML = '<div class="suggest-loading"><span class="spinner"></span> Recherche en cours…</div>';

            try {
                const response = await fetch(appUrl('book/search', 'q=' + encodeURIComponent(term)), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();

                if (input.value.trim() !== term) return; // réponse obsolète
                results.innerHTML = data.html;
                input.setAttribute('aria-expanded', 'true');
            } catch {
                results.hidden = true;
            }
        }

        input.addEventListener('input', () => {
            const term = input.value.trim();
            applyCardFilter(term); // filtre de la page (client-side)

            clearTimeout(debounce);
            if (term.length < 2) {
                results.hidden = true;
                results.innerHTML = '';
                return;
            }
            debounce = setTimeout(() => fetchSuggestions(term), 250);
        });

        /* -- Filtres rapides (chips) sur la page affichée -- */
        chips.forEach((chip) => {
            chip.addEventListener('click', () => {
                chips.forEach((c) => {
                    c.classList.toggle('is-active', c === chip);
                    c.setAttribute('aria-pressed', String(c === chip));
                });
                activeChip = chip.dataset.filter;
                applyCardFilter(input.value.trim());
            });
        });

        function applyCardFilter(term) {
            if (!grid || cards.length === 0) return;

            const q = term.toLowerCase();
            const field = activeChip === 'all' ? null : FIELDS[activeChip];

            let visible = 0;
            cards.forEach((card) => {
                const text = field ? (card.dataset[field] || '') : (card.dataset.title + ' ' + card.dataset.author + ' ' + card.dataset.edition);
                const show = q === '' || (!field && (card.dataset.title + ' ' + card.dataset.author + ' ' + card.dataset.edition).includes(q))
                    || (field && text.includes(q));
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            let emptyBox = $('.grid-empty', grid.parentElement);
            if (visible === 0) {
                if (!emptyBox) {
                    emptyBox = document.createElement('div');
                    emptyBox.className = 'empty-state grid-empty';
                    emptyBox.innerHTML = '<span class="empty-icon"><svg class="icon"><use href="#i-search"/></svg></span><h2>Rien sur cette page</h2><p>Aucune fiche ne correspond à ce filtre sur la page courante.</p>';
                    grid.after(emptyBox);
                }
            } else if (emptyBox) {
                emptyBox.remove();
            }

            if (count) {
                count.textContent = (q !== '' || activeChip !== 'all')
                    ? visible + ' / ' + cards.length + ' sur cette page'
                    : originalCount;
            }
        }

        // Ferme les suggestions : clic extérieur, Escape, soumission.
        document.addEventListener('click', (event) => {
            if (!results.contains(event.target) && event.target !== input) {
                results.hidden = true;
                input.setAttribute('aria-expanded', 'false');
            }
        });

        form && form.addEventListener('submit', () => {
            results.hidden = true;
        });
    }

    /* ----------------------------------------------------------
       3. Toasts (messages flash) — auto-fermeture
       ---------------------------------------------------------- */
    function initToasts() {
        const region = $('#toast-region');
        if (!region) return;

        // Clique sur un toast => fermeture immédiate.
        region.addEventListener('click', (e) => {
            if (e.target.closest('.toast')) dismiss($(e.target.closest('.toast')));
        });

        $$('.toast', region).forEach((toast) => {
            const ms = 4200;
            toast.style.setProperty('--toast-ms', ms + 'ms');
            setTimeout(() => dismiss(toast), ms);
        });

        function dismiss(toast) {
            if (!toast || toast.classList.contains('is-leaving')) return;
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 260);
        }
    }

    /* ----------------------------------------------------------
       4. Modale de confirmation (remplace window.confirm)
       ---------------------------------------------------------- */
    function initConfirmModal() {
        const modal = $('#confirm-modal');
        if (!modal) return;

        const titleEl = $('#confirm-title', modal);
        const textEl = $('#confirm-text', modal);
        const confirmBtn = $('[data-modal-confirm]', modal);
        let pendingForm = null;
        let lastFocus = null;

        const open = (title, text) => {
            titleEl.textContent = title;
            textEl.textContent = text;
            lastFocus = document.activeElement;
            modal.classList.add('is-open');
            confirmBtn.focus();
        };
        const close = () => {
            modal.classList.remove('is-open');
            if (lastFocus) lastFocus.focus();
        };

        // Intercepte les formulaires portant data-confirm.
        $$('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                pendingForm = form;
                open(
                    form.dataset.confirmTitle || 'Confirmer la suppression ?',
                    form.dataset.confirm || 'Êtes-vous sûr de vouloir continuer ?'
                );
            });
        });

        confirmBtn.addEventListener('click', () => {
            if (pendingForm) {
                const f = pendingForm;
                pendingForm = null;
                close();
                f.submit(); // form.submit() ne redéclenche pas 'submit' => pas de boucle
            }
        });

        $$('[data-modal-close]', modal).forEach((btn) => btn.addEventListener('click', () => {
            pendingForm = null;
            close();
        }));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                pendingForm = null;
                close();
            }
        });
    }

    /** Repli compatible API précédente (encore utilisable dans le HTML). */
    PL.ConfirmDelete = function ConfirmDelete(message) {
        return window.confirm(message || 'Êtes-vous sûr de vouloir continuer ?');
    };

    /* ----------------------------------------------------------
       5. Panneau latéral (slide-over) : ajout / édition admin
       ---------------------------------------------------------- */
    function initDrawer() {
        const drawer = $('[data-drawer]');
        if (!drawer) return;

        const backdrop = $('[data-drawer-close]');
        const form = $('[data-book-form]');
        const titleEl = $('[data-drawer-title]');
        const subEl = $('[data-drawer-sub]');
        const submitBtn = $('[data-book-submit]');
        const createBtn = $('[data-open-create]');
        const uploadZone = $('[data-upload-zone]', drawer);
        let lastFocus = null;

        const field = (name) => form ? $('[name="' + name + '"]', form) : null;

        const open = () => {
            lastFocus = document.activeElement;
            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            backdrop.classList.add('is-open');
            backdrop.setAttribute('aria-hidden', 'false');
            const first = form ? $('.form-control', form) : null;
            if (first) first.focus();
        };
        const close = () => {
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            backdrop.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');
            if (lastFocus) lastFocus.focus();
        };

        // Ajout (vide).
        createBtn && createBtn.addEventListener('click', () => {
            if (form) {
                form.reset();
                form.setAttribute('action', appUrl('admin/create'));
                field('titre') && (field('titre').value = '');
                const num = field('nombre_exemplaire');
                if (num) num.value = 1;
            }
            if (uploadZone) resetUploadZone(uploadZone, '');
            titleEl.textContent = 'Ajouter un livre';
            subEl.textContent = 'Renseignez les informations du nouveau livre.';
            submitBtn.innerHTML = '<svg class="icon"><use href="#i-check"/></svg> Enregistrer';
            open();
        });

        // Édition (pré-remplie depuis la ligne du tableau).
        $$('[data-open-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('[data-book-row]');
                if (!row || !form) return;

                form.reset();
                form.setAttribute('action', row.dataset.editUrl); // route admin/edit?id=X
                field('titre') && (field('titre').value = row.dataset.editTitre || '');
                field('auteur') && (field('auteur').value = row.dataset.editAuteur || '');
                field('maison_edition') && (field('maison_edition').value = row.dataset.editMaison || '');
                field('nombre_exemplaire') && (field('nombre_exemplaire').value = row.dataset.editExemplaires || '');
                field('description') && (field('description').value = row.dataset.editDescription || '');
                if (uploadZone) resetUploadZone(uploadZone, row.dataset.editCover || '');

                titleEl.textContent = 'Modifier le livre';
                subEl.textContent = row.dataset.editSub || 'Mettre à jour les informations du livre.';
                submitBtn.innerHTML = '<svg class="icon"><use href="#i-check"/></svg> Enregistrer les modifications';
                open();
            });
        });

        // Fermeture.
        $$('[data-drawer-close]').forEach((btn) => btn.addEventListener('click', close));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
        });
    }

    /* ----------------------------------------------------------
       5b. Zone d'envoi de couverture (aperçu + drag & drop)
       ---------------------------------------------------------- */
    function resetUploadZone(zone, serverCoverUrl) {
        const input = $('[data-upload-input]', zone);
        const preview = $('[data-upload-preview]', zone);
        const placeholder = $('[data-upload-placeholder]', zone);
        const labelText = $('[data-upload-label-text]', zone);
        const removeBtn = $('[data-upload-remove]', zone);
        const filename = $('[data-upload-filename]', zone);
        const group = zone.closest('[data-upload-group]');
        const flagWrap = group ? $('[data-upload-flag-wrap]', group) : null;
        const flag = group ? $('[data-upload-remove-flag]', group) : null;

        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        preview.dataset.objectUrl = '';
        input.value = '';

        zone.dataset.serverCover = serverCoverUrl || '';
        preview.src = serverCoverUrl || '';
        preview.classList.remove('is-visible');
        placeholder.classList.toggle('is-visible', !serverCoverUrl);
        filename.textContent = '';
        removeBtn.hidden = true;

        labelText.textContent = serverCoverUrl ? 'Remplacer l\u2019image' : 'Choisir une image';

        if (serverCoverUrl) {
            preview.classList.add('is-visible');
        }
        if (flagWrap) flagWrap.hidden = !serverCoverUrl;
        if (flag) flag.checked = false;
    }

    function initUploadZones() {
        $$('[data-upload-zone]').forEach((zone) => {
            const input = $('[data-upload-input]', zone);
            const preview = $('[data-upload-preview]', zone);
            const placeholder = $('[data-upload-placeholder]', zone);
            const labelText = $('[data-upload-label-text]', zone);
            const removeBtn = $('[data-upload-remove]', zone);
            const filename = $('[data-upload-filename]', zone);
            const group = zone.closest('[data-upload-group]');
            const flagWrap = group ? $('[data-upload-flag-wrap]', group) : null;
            const flag = group ? $('[data-upload-remove-flag]', group) : null;

            // État initial servi par le serveur (page autonome admin/form.php).
            zone.dataset.serverCover = (preview.classList.contains('is-visible') && preview.src) ? preview.src : '';

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) {
                    resetUploadZone(zone, zone.dataset.serverCover);
                    return;
                }
                if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
                preview.dataset.objectUrl = URL.createObjectURL(file);
                preview.src = preview.dataset.objectUrl;
                preview.classList.add('is-visible');
                placeholder.classList.remove('is-visible');
                filename.textContent = file.name;
                labelText.textContent = 'Annuler la sélection';
                removeBtn.hidden = false;
                if (flag) flag.checked = false;
                if (flagWrap) flagWrap.hidden = true;
                if (group) group.classList.remove('has-error');
            });

            removeBtn.addEventListener('click', () => resetUploadZone(zone, zone.dataset.serverCover));

            ['dragenter', 'dragover'].forEach((type) =>
                zone.addEventListener(type, (e) => {
                    e.preventDefault();
                    zone.classList.add('is-drag');
                }));
            ['dragleave', 'drop'].forEach((type) =>
                zone.addEventListener(type, (e) => {
                    e.preventDefault();
                    zone.classList.remove('is-drag');
                }));
            zone.addEventListener('drop', (e) => {
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            const label = $('[data-upload-label]', zone);
            if (label) {
                label.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        input.click();
                    }
                });
            }
        });
    }

    /* ----------------------------------------------------------
       6. Saisie de note par étoiles (formulaire d'avis)
       ---------------------------------------------------------- */
    function initStarRating() {
        $$('[data-star-input]').forEach((wrap) => {
            const labels = Array.from($$('.star-input-label', wrap));
            const inputs = Array.from($$('input[type="radio"]', wrap));
            const value = $('[data-star-value]', wrap);

            const paint = (limit) => {
                labels.forEach((label) => {
                    const star = Number(label.querySelector('input').value);
                    label.classList.toggle('is-checked', star <= limit);
                });
            };

            const render = () => {
                const checked = inputs.find((r) => r.checked);
                paint(checked ? Number(checked.value) : 0);
                if (value) {
                    value.textContent = checked
                        ? checked.value + ' étoile' + (Number(checked.value) > 1 ? 's' : '')
                        : 'Cliquez pour noter';
                }
            };

            inputs.forEach((r) => r.addEventListener('change', render));
            labels.forEach((label) => {
                const star = Number(label.querySelector('input').value);
                label.addEventListener('mouseenter', () => paint(star));
                label.addEventListener('focus', () => paint(star));
            });
            wrap.addEventListener('mouseleave', render);
            wrap.addEventListener('focusout', render);
            render();
        });
    }

    /* ----------------------------------------------------------
       7. Bouton "esprit occupé" pendant l'envoi (formulaire data-busy)
       ---------------------------------------------------------- */
    function initBusySubmit() {
        $$('form[data-busy]').forEach((form) => {
            const btn = $('button[type="submit"]', form);
            if (!btn) return;
            form.addEventListener('submit', () => btn.classList.add('is-loading'));
        });
    }

    /* ----------------------------------------------------------
       8. Apparition au défilement (réserve si motion réduite)
       ---------------------------------------------------------- */
    function initReveal() {
        const targets = $$('.reveal, .reveal-stagger');
        if (!targets.length || REDUCED || !('IntersectionObserver' in window)) {
            targets.forEach((t) => t.classList.add('is-in'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        targets.forEach((t) => io.observe(t));
    }

    /* ----------------------------------------------------------
       9. Toast dynamique à la demande
       ---------------------------------------------------------- */
    function showToast(message, type = 'success') {
        const region = $('#toast-region');
        if (!region) return;
        const icon = type === 'success' ? 'i-check' : (type === 'error' ? 'i-alert' : 'i-info');
        const toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.setAttribute('role', 'status');
        toast.innerHTML = `
            <span class="toast-icon"><svg class="icon"><use href="#${icon}"/></svg></span>
            <p class="toast-msg">${message}</p>
            <span class="toast-bar" aria-hidden="true"></span>
        `;
        const ms = 4000;
        toast.style.setProperty('--toast-ms', ms + 'ms');
        region.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 260);
        }, ms);
    }

    /* ----------------------------------------------------------
       10. Fonctionnalités interactives du Blog (Upload photo & partage)
       ---------------------------------------------------------- */
    function initBlogFeatures() {
        // Prévisualisation interactive de la photo dans le formulaire de rédaction
        const dropzone = $('#photo-dropzone');
        const input = $('#photo-input');
        const emptyState = $('#dropzone-empty');
        const previewState = $('#dropzone-preview');
        const previewImg = $('#preview-img');
        const previewName = $('#preview-name');
        const removeBtn = $('#btn-remove-photo');

        if (dropzone && input) {
            const handleFile = (file) => {
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (previewImg) previewImg.src = e.target.result;
                    if (previewName) previewName.textContent = file.name;
                    if (emptyState) emptyState.style.display = 'none';
                    if (previewState) previewState.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            };

            input.addEventListener('change', () => {
                if (input.files && input.files[0]) {
                    handleFile(input.files[0]);
                }
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    input.value = '';
                    if (previewImg) previewImg.src = '';
                    if (previewState) previewState.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'flex';
                });
            }

            // Drag & Drop
            ['dragenter', 'dragover'].forEach((type) => {
                dropzone.addEventListener(type, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach((type) => {
                dropzone.addEventListener(type, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('is-dragover');
                });
            });

            dropzone.addEventListener('drop', (e) => {
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    input.files = e.dataTransfer.files;
                    handleFile(e.dataTransfer.files[0]);
                }
            });
        }

        // Copie du lien de l'article
        const copyLinkBtn = $('#btn-copy-link');
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', async () => {
                const urlToCopy = copyLinkBtn.dataset.copyUrl || window.location.href;
                try {
                    await navigator.clipboard.writeText(urlToCopy);
                    showToast('Lien de l\'article copié dans le presse-papier !', 'success');
                } catch {
                    showToast('Lien : ' + urlToCopy, 'info');
                }
            });
        }
    }

    /* ----------------------------------------------------------
       11. Fonctionnalités interactives de Contact (Copie email & FAQ)
       ---------------------------------------------------------- */
    function initContactFeatures() {
        // Copie de l'email
        const copyEmailBtn = $('#btn-copy-email');
        if (copyEmailBtn) {
            copyEmailBtn.addEventListener('click', async () => {
                const email = copyEmailBtn.dataset.copy || 'contact@bibliotheque.sn';
                try {
                    await navigator.clipboard.writeText(email);
                    showToast('Adresse email (' + email + ') copiée !', 'success');
                } catch {
                    showToast('Email : ' + email, 'info');
                }
            });
        }

        // Accordéon FAQ
        const accordion = $('#faq-accordion');
        if (accordion) {
            $$('.accordion-trigger', accordion).forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const item = trigger.closest('.accordion-item');
                    const isOpen = item.classList.contains('is-open');

                    // Fermer les autres accordéons
                    $$('.accordion-item', accordion).forEach((other) => {
                        other.classList.remove('is-open');
                        const otherBtn = $('.accordion-trigger', other);
                        if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
                    });

                    if (!isOpen) {
                        item.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        }

        // Formulaire de contact (animation submit)
        const contactForm = $('#contact-form');
        const submitBtn = $('#btn-submit-contact');
        if (contactForm && submitBtn) {
            contactForm.addEventListener('submit', () => {
                submitBtn.classList.add('is-loading');
            });
        }
    }

    /* ----------------------------------------------------------
       initCountUp — Anime les nombres dans les stat cards admin
       ---------------------------------------------------------- */
    function initCountUp() {
        const els = $$('[data-countup]');
        if (!els.length) return;

        const animate = (el) => {
            const target = parseInt(el.dataset.countup, 10) || 0;
            if (REDUCED || target === 0) { el.textContent = target.toLocaleString('fr-FR'); return; }

            const duration = Math.min(1200, 400 + target * 2);
            const start = performance.now();

            const step = (now) => {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                // easeOutExpo
                const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                el.textContent = Math.round(eased * target).toLocaleString('fr-FR');
                if (progress < 1) requestAnimationFrame(step);
            };

            requestAnimationFrame(step);
        };

        // Déclenche quand l'élément entre dans le viewport
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        els.forEach(el => io.observe(el));
    }

    /* ----------------------------------------------------------
       initAdminCharts — Graphiques Chart.js du dashboard admin
       ---------------------------------------------------------- */
    function initAdminCharts() {
        const dataEl = document.getElementById('admin-chart-data');
        if (!dataEl || typeof Chart === 'undefined') return;

        let chartData = {};
        try { chartData = JSON.parse(dataEl.textContent); } catch { return; }

        // Helpers couleurs CSS
        const cs = getComputedStyle(document.documentElement);
        const teal   = '#0F766E';
        const tealSoft = 'rgba(15,118,110,.12)';
        const blue   = '#2563EB';
        const blueSoft = 'rgba(37,99,235,.12)';
        const muted  = cs.getPropertyValue('--muted').trim() || '#64748B';
        const border = cs.getPropertyValue('--border').trim() || '#E2E8F0';
        const ink    = cs.getPropertyValue('--ink').trim() || '#0F172A';

        const fontFamily = "'Plus Jakarta Sans', system-ui, sans-serif";

        Chart.defaults.font.family = fontFamily;
        Chart.defaults.color = muted;

        // Grille par défaut
        const gridOpts = {
            color: border,
            drawBorder: false,
        };
        const tickOpts = { font: { size: 11, weight: '500' }, color: muted };

        /* ---------- 1. Graphique emprunts par mois (barres) ---------- */
        const loanCtx = document.getElementById('chart-loans');
        if (loanCtx && chartData.loanTrend && chartData.loanTrend.length) {
            const trend = chartData.loanTrend;
            new Chart(loanCtx, {
                type: 'bar',
                data: {
                    labels: trend.map(d => d.label),
                    datasets: [{
                        label: 'Emprunts',
                        data: trend.map(d => d.count),
                        backgroundColor: trend.map((_, i, arr) =>
                            i === arr.length - 1 ? teal : 'rgba(15,118,110,.55)'
                        ),
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: .65,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: REDUCED ? 0 : 900, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: fontFamily, size: 12, weight: '700' },
                            bodyFont:  { family: fontFamily, size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} emprunt${ctx.parsed.y > 1 ? 's' : ''}`,
                            }
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: tickOpts, border: { display: false } },
                        y: {
                            grid: gridOpts,
                            ticks: { ...tickOpts, precision: 0 },
                            border: { display: false },
                            beginAtZero: true,
                        }
                    }
                }
            });
        }

        /* ---------- 2. Donut — statut des livres ---------- */
        const booksCtx = document.getElementById('chart-books');
        if (booksCtx && chartData.bookStatus && chartData.bookStatus.length) {
            const bs = chartData.bookStatus;
            new Chart(booksCtx, {
                type: 'doughnut',
                data: {
                    labels: bs.map(d => d.label),
                    datasets: [{
                        data: bs.map(d => d.value),
                        backgroundColor: bs.map(d => d.color),
                        borderColor: '#FFFFFF',
                        borderWidth: 3,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    animation: { duration: REDUCED ? 0 : 1000, animateRotate: true },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: fontFamily, size: 12, weight: '700' },
                            bodyFont:  { family: fontFamily, size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.label} : ${ctx.parsed} livre${ctx.parsed > 1 ? 's' : ''}`,
                            }
                        },
                    }
                }
            });
        }

        /* ---------- 3. Courbe — croissance utilisateurs ---------- */
        const usersCtx = document.getElementById('chart-users');
        if (usersCtx && chartData.userGrowth && chartData.userGrowth.length) {
            const ug = chartData.userGrowth;
            new Chart(usersCtx, {
                type: 'line',
                data: {
                    labels: ug.map(d => d.label),
                    datasets: [{
                        label: 'Inscriptions',
                        data: ug.map(d => d.count),
                        borderColor: blue,
                        backgroundColor: blueSoft,
                        borderWidth: 2.5,
                        pointBackgroundColor: blue,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: .4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: REDUCED ? 0 : 900, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: fontFamily, size: 12, weight: '700' },
                            bodyFont:  { family: fontFamily, size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} inscription${ctx.parsed.y > 1 ? 's' : ''}`,
                            }
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: tickOpts, border: { display: false } },
                        y: {
                            grid: gridOpts,
                            ticks: { ...tickOpts, precision: 0 },
                            border: { display: false },
                            beginAtZero: true,
                        }
                    }
                }
            });
        }
    }

    /* ----------------------------------------------------------
       Démarrage
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        initNavToggle();
        initSearch();
        initToasts();
        initConfirmModal();
        initDrawer();
        initUploadZones();
        initStarRating();
        initBusySubmit();
        initReveal();
        initBlogFeatures();
        initContactFeatures();
        initCountUp();
        initAdminCharts();
    });
})();