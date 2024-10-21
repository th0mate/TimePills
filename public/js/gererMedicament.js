let idTraitement = 0;

document.addEventListener('DOMContentLoaded', function () {
    const dots = document.querySelectorAll('.dots');
    const menu = document.getElementById('menu');


    dots.forEach(dot => {
        dot.addEventListener('click', function (event) {
            event.preventDefault();
            const rect = dot.getBoundingClientRect();
            menu.style.top = `${rect.top + window.scrollY}px`;
            menu.style.left = `${rect.left + window.scrollX}px`;
            menu.style.display = 'block';
            idTraitement = dot.dataset.traitementId;
        });
    });

    document.addEventListener('click', function (event) {
        if (!menu.contains(event.target) && !event.target.classList.contains('dots')) {
            menu.style.display = 'none';
            idTraitement = 0;
        }
    });
});

async function modifierTraitement(idTraitement) {
    alert('Modifier le traitement');
    // Ajoutez ici le code pour modifier le traitement
}

async function supprimerTraitement(idTraitement) {
    alert('Supprimer le traitement');
    // Ajoutez ici le code pour supprimer le traitement
}
