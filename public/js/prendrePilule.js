async function prendrePilule() {
    const id = event.target.dataset.piluleId;
    let URL = Routing.generate('prendrePilule', {"idPilule": id});
    const response = await fetch(URL, {method: "POST"});
    await prendrePiluleCalendrier(id);

    document.querySelector(`[data-pilule-id="${id}"]`).classList.remove('bouton');
    document.querySelector(`[data-pilule-id="${id}"]`).classList.add('check');
    document.querySelector(`[data-pilule-id="${id}"]`).innerHTML = 'Pris';
    document.querySelector(`[data-pilule-id="${id}"]`).removeAttribute('onclick');

}

async function test() {
    let URLGetId = Routing.generate('piluleEstEnPause', {"idPilule": 1});
    const response = await fetch(URLGetId, {method: "POST"});
    console.log(await response.json());
}
test();