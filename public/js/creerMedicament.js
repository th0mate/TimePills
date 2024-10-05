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