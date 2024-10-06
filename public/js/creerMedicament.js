function changerDisplayAvecPause() {
    document.querySelectorAll('.avecPause').forEach(function (element) {
        if (element.style.display === 'none') {
            element.style.display = 'block';
        } else {
            element.style.display = 'none';
        }
    });
}

changerDisplayAvecPause();

document.querySelector('#pilule_nbPilulesPlaquette').addEventListener('input', function () {
    const valeur = this.value;
    const inputNbJoursPause = document.querySelector('#pilule_nbJoursPause');
    inputNbJoursPause.value = 28 - valeur;
});