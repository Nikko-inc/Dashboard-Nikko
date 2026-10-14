//On recupere l'id du projet et le role de l'utilisateur sur ce projet dans le body
const projetId = Number(document.body.dataset.projetId);
const estProprietaire = document.body.dataset.role === 'proprietaire';
let taches = [];
let membres = [];


// COMMUNICATION AVEC L'API

// Envoie POST / PATCH / DELETE à l'API des tâches
async function appelerApi(methode, donnees) {
    const reponse = await fetch('api/taches.php', {
        method: methode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(donnees)
    });
    const resultat = await reponse.json();
    if (!reponse.ok) {
        // 4xx / 5xx : on transforme la réponse d'erreur en exception
        throw new Error(resultat.erreur || 'Erreur serveur');
    }
    return resultat;
}

// Demande la liste des tâches (GET) puis redessine
async function chargerTaches() {
    const reponse = await fetch(`api/taches.php?projet_id=${projetId}`);
    if (!reponse.ok) {
        alert('Impossible de charger les tâches.');
        return;
    }
    taches = await reponse.json();
    dessiner();
}

// CALCULS 

function calculerProgression(liste) {
    if (liste.length === 0) return 0;
    const terminees = liste.filter(t => t.statut === 'termine').length;
    return Math.round((terminees / liste.length) * 100);
}

// Nombre de jours entre aujourd'hui et la date d'échéance (négatif = dépassée)
function joursRestants(dateEcheance) {
    const aujourdhui = new Date();
    aujourdhui.setHours(0, 0, 0, 0);                         // minuit ce matin
    const [annee, mois, jour] = dateEcheance.split('-').map(Number);
    const echeance = new Date(annee, mois - 1, jour);        // les mois commencent à 0
    return Math.round((echeance - aujourdhui) / 86400000);   // ms → jours
}

// Quelle classe CSS donner à la tâche selon son échéance 
function classeEcheance(tache) {
    if (!tache.echeance || tache.statut === 'termine') return '';
    const jours = joursRestants(tache.echeance);
    if (jours < 0) return 'echeance-depassee';
    if (jours <= 2) return 'echeance-proche';
    return '';
}


// AFFICHAGE : tout est redessiné à partir du tableau « taches »

function dessiner() {
    dessinerBarre();
    dessinerListe();
}

function dessinerBarre() {
    const pourcentage = calculerProgression(taches);
    const barre = document.getElementById('barre-progression');
    barre.style.width = pourcentage + '%';
    barre.textContent = pourcentage + '%';
}

function dessinerListe() {
    const liste = document.getElementById('liste-taches');
    liste.innerHTML = '';   // on vide, puis on reconstruit

    taches.forEach(tache => {
        const li = document.createElement('li');

        const classe = classeEcheance(tache);
        if (classe) li.classList.add(classe);
        if (tache.statut === 'termine') li.classList.add('termine');

        // Case à cocher
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = tache.statut === 'termine';
        checkbox.addEventListener('change', () => changerStatut(tache, checkbox.checked));

        // Titre (textContent : du texte, jamais du HTML interprété)
        const titre = document.createElement('span');
        titre.className = 'titre-tache';
        titre.textContent = tache.titre;

        // Échéance modifiable (pour repousser la date)
        const champDate = document.createElement('input');
        champDate.type = 'date';
        champDate.value = tache.echeance || '';
        champDate.addEventListener('change', () => changerEcheance(tache, champDate.value));

        const menu = document.createElement('select');
        menu.appendChild(new Option('Non assignée', ''));
        membres.forEach(m => menu.appendChild(new Option(m.nom, m.id)));
        menu.value = tache.assigne_a ?? '';
        menu.addEventListener('change', () => assignerTache(tache, menu.value));

        li.append(checkbox, titre, menu, champDate, );

        // Bouton supprimer : affiché au propriétaire seulement.
        if (estProprietaire) {
            const bouton = document.createElement('button');
            bouton.textContent = 'Supprimer';
            bouton.addEventListener('click', () => supprimerTache(tache));
            li.appendChild(bouton);
        }

        liste.appendChild(li);
    });
}


// ACTIONS DE L'UTILISATEUR

//changer le statut d'une tache ('terminée', 'a_faire')
async function changerStatut(tache, estCoche) {
    const statut = estCoche ? 'termine' : 'a_faire';
    try {
        await appelerApi('PATCH', { id: tache.id, statut });
        tache.statut = statut;
    } catch (erreur) {
        alert(erreur.message);
    }
    dessiner();   // redessine dans tous les cas (en cas d'échec, la case revient à son état réel)
}

//fonction pour changer l'echeance d'une tache
async function changerEcheance(tache, valeur) {
    const echeance = valeur || null;   // champ vidé → pas d'échéance
    try {
        await appelerApi('PATCH', { id: tache.id, echeance });
        tache.echeance = echeance;
    } catch (erreur) {
        alert(erreur.message);
    }
    dessiner();
}

//assigner une tache a un membre
async function assignerTache(tache, valeur) {
    const idMembre = valeur === '' ? null : Number(valeur);
    try {
        await appelerApi('PATCH', { id: tache.id, assigne_a: idMembre });
        tache.assigne_a = idMembre;
    } catch (erreur) {
        alert(erreur.message);
    }
    dessiner();
}

//fonction pour afficher les membres d'un projet sur un projet specifique
async function chargerMembres() {
    const reponse = await fetch(`api/membres.php?projet_id=${projetId}`);
    if (!reponse.ok) return;
    membres = await reponse.json();

    const liste = document.getElementById('liste-membres');
    liste.innerHTML = '';
    membres.forEach(m => {
        const li = document.createElement('li');
        li.textContent = m.nom + (m.role === 'proprietaire' ? ' (propriétaire)' : '');
        liste.appendChild(li);
    });
}

//supprimer une tache
async function supprimerTache(tache) {
    if (!confirm(`Supprimer « ${tache.titre} » ?`)) return;
    try {
        await appelerApi('DELETE', { id: tache.id });
        taches = taches.filter(t => t.id !== tache.id);
    } catch (erreur) {
        alert(erreur.message);
    }
    dessiner();
}

// Ajout d'une tâche
const formTaches = document.getElementById('form-nouvelle-tache')
formTaches.addEventListener('submit', async (e) => {
    e.preventDefault();   // empêche le rechargement de la page 
    const champTitre = document.getElementById('titre-tache');
    const champDate  = document.getElementById('echeance-tache');
    const champDesc  = document.getElementById('description');

    const titre = champTitre.value.trim();
    if (!titre) return;

    try {
        await appelerApi('POST', {
            projet_id: projetId,
            titre,
            echeance: champDate.value || null,
            description: champDesc
        });
        champTitre.value = '';
        champDate.value = '';
        champDesc.value = '';
        await chargerTaches();   // on recharge la liste depuis la base
    } catch (erreur) {
        alert(erreur.message);
    }
});

// Ajout d'un membre
const formMembre = document.getElementById('form-nouveau-membre');
if (formMembre) {   // ce formulaire n'existe que pour le propriétaire
    formMembre.addEventListener('submit', async (e) => {
        e.preventDefault();
        const champ = document.getElementById('email-membre');
        const reponse = await fetch('api/membres.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ projet_id: projetId, email: champ.value.trim() })
        });
        const resultat = await reponse.json();
        if (!reponse.ok) {
            alert(resultat.erreur);
            return;
        }
        champ.value = '';
        await chargerMembres();
        dessiner();   // les menus d'assignation proposent le nouveau membre
    });
}

// DÉMARRAGE
async function demarrer() {
    await chargerMembres();   
    await chargerTaches();
}
demarrer();