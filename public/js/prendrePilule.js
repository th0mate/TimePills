async function prendrePilule() {
    const id = event.target.dataset.piluleId;
    let URL = Routing.generate('prendrePilule', {"idPilule": id});
    const response = await fetch(URL, {method: "POST"});

    //TODO : supprimer l'événement du calendrier
    document.querySelector(`button[data-pilule-id="${id}"]`).classList.remove('bouton');
    document.querySelector(`button[data-pilule-id="${id}"]`).classList.add('check');
    document.querySelector(`button[data-pilule-id="${id}"]`).innerHTML = 'Pris';
    document.querySelector(`button[data-pilule-id="${id}"]`).removeAttribute('onclick');

}