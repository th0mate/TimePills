document.querySelector('#utilisateur_adresseMail').addEventListener('input', async function (e) {
    const adresseMail = e.target.value;

    if (adresseMail.includes('@')) {
        if (await adresseMailEstDejaPrise(adresseMail)) {
            afficherMessageFlash('Cette adresse mail est déjà prise', 'warning');
            e.target.setCustomValidity('Cette adresse mail est déjà prise');
        } else {
            e.target.setCustomValidity('');
        }

        e.target.reportValidity();

    }

});

/**
 * Vérifie si l'adresse mail est déjà prise
 * @param adresseMail {string} - Adresse mail à vérifier
 * @returns {Promise<any>} - Résultat de la requête
 */
async function adresseMailEstDejaPrise(adresseMail) {
    let URL = Routing.generate('verifier_email', {"adresseMail": adresseMail});
    const response = await fetch(URL, {method: "POST"});
    return await response.json();

}