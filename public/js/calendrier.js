let calendar;
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        buttonText: {
            today: 'Aujourd\'hui'
        },
        eventDidMount: function (info) {
            if (info.event.title.includes('pris')) {
                info.el.style.backgroundColor = '#258700';
            } else if (info.event.title.includes('à prendre')) {
                info.el.style.backgroundColor = '#d5a900';
            } else {
                info.el.style.backgroundColor = '#7f15ff';
            }
            info.el.style.border = 'none';
            info.el.classList.add('fc-event-wrap');
        }
    });
    calendar.render();

    // Ajoute les événements au calendrier
    ajouterEvenementsCalendrierSelonPilule(calendar);
});


/**
 * Ajoute des événements au calendrier, en fonction des pilules de l'utilisateur et de ses données
 * @param calendar {FullCalendar.Calendar} - Calendrier
 * @returns {Promise<void>} - Rien
 */
async function ajouterEvenementsCalendrierSelonPilule(calendar) {
    let URLGetId = Routing.generate('listeIdPilules');
    const response = await fetch(URLGetId, {method: "POST"});
    const listeIdPilules = await response.json();


    for (let idPilule of listeIdPilules) {
        let URLGetInfosPilule = Routing.generate('infosPilule', {"idPilule": idPilule});
        const response = await fetch(URLGetInfosPilule, {method: "POST"});
        const pilule = await response.json();

        if (pilule.nbJoursPause === null) {
            let date = new Date();
            for (let i = 0; i < 180; i++) {
                date.setDate(date.getDate() + 1);
                const event = {
                    title: pilule.libelle + ' à prendre',
                    start: date,
                    allDay: true
                };
                calendar.addEvent(event);
            }
        } else {
            const dateDerniereReprise = new Date(pilule.dateDerniereReprise);

            if (dateDerniereReprise === new Date(new Date().setHours(0, 0, 0, 0))) {

                for (let i = 0; i < pilule.nbPilulesPlaquette; i++) {
                    const event = {
                        title: pilule.libelle + ' à prendre',
                        start: dateDerniereReprise,
                        allDay: true
                    };
                    calendar.addEvent(event);
                    dateDerniereReprise.setDate(dateDerniereReprise.getDate() + 1);
                }

                for (let i = 0; i < pilule.nbJoursPause; i++) {
                    const event = {
                        title: 'Pause - ' + pilule.libelle,
                        start: dateDerniereReprise,
                        allDay: true
                    };
                    calendar.addEvent(event);
                    dateDerniereReprise.setDate(dateDerniereReprise.getDate() + 1);
                }

            } else {
                let date = dateDerniereReprise;
                for (let i = 0; i < pilule.nbPilulesPlaquette; i++) {
                    if (date < new Date(new Date().setHours(0, 0, 0, 0))) {
                        const event = {
                            title: pilule.libelle + ' pris',
                            start: date,
                            allDay: true
                        };
                        calendar.addEvent(event);
                    } else {
                        const event = {
                            title: pilule.libelle + ' à prendre',
                            start: date,
                            allDay: true
                        };
                        calendar.addEvent(event);
                    }
                    date.setDate(date.getDate() + 1);
                }

                for (let i = 0; i < pilule.nbJoursPause; i++) {
                    const event = {
                        title: 'Pause - ' + pilule.libelle,
                        start: date,
                        allDay: true
                    };
                    calendar.addEvent(event);
                    date.setDate(date.getDate() + 1);
                }

                //on fait un cycle supplémentaire

                for (let i = 0; i < pilule.nbPilulesPlaquette; i++) {
                    const event = {
                        title: pilule.libelle + ' à prendre',
                        start: date,
                        allDay: true
                    };
                    calendar.addEvent(event);
                    date.setDate(date.getDate() + 1);
                }

                for (let i = 0; i < pilule.nbJoursPause; i++) {
                    const event = {
                        title: 'Pause - ' + pilule.libelle,
                        start: date,
                        allDay: true
                    };
                    calendar.addEvent(event);
                    date.setDate(date.getDate() + 1);
                }


            }
        }

        const URLDatesPrises = Routing.generate('prisesPilule', {"idPilule": idPilule});
        const responseDatesPrises = await fetch(URLDatesPrises, {method: "POST"});
        pilule.datesPrises = await responseDatesPrises.json();

        console.log(pilule.datesPrises);


        if (pilule.datesPrises.length > 0) {

            for (let date of pilule.datesPrises) {
                //exemple d'une ligne du tableau :
                //date = {datePrise: '2024-10-05T11:29:35+00:00'}

                console.log(date.datePrise);

                let dateUtile = new Date(date.datePrise);
                //on met la date en France
                dateUtil = new Date(dateUtile.setHours(dateUtile.getHours() - 2));

                console.log(dateUtile);
                //heure au format hh:mm. L'heure est déjà en France
                const heure = dateUtile.getHours().toString().padStart(2, '0') + ':' + dateUtile.getMinutes().toString().padStart(2, '0');

                const events = calendar.getEvents();

                events.forEach(event => {
                    const eventDate = new Date(event.start.setHours(0, 0, 0, 0));
                    const utileDate = new Date(dateUtile.setHours(0, 0, 0, 0));
                    if (event.title.includes(pilule.libelle) && eventDate.getTime() === utileDate.getTime()) {
                        event.remove();
                    }
                });

                const event = {
                    title: `${pilule.libelle} pris à ${heure}`,
                    start: dateUtile,
                    allDay: true
                };
                calendar.addEvent(event);
            }
        }




    }
}

/**
 * Prend une pilule en supprimant l'événement du calendrier, et en ajoutant un événement '{libelle} pris'
 */
async function prendrePiluleCalendrier(idPilule) {
    // Récupère les informations de la pilule
    let URLGetInfosPilule = Routing.generate('infosPilule', {"idPilule": idPilule});
    const response = await fetch(URLGetInfosPilule, {method: "POST"});
    const pilule = await response.json();

    // Récupère la date d'aujourd'hui
    let date = new Date(new Date().setHours(0, 0, 0, 0));

    // Supprime l'événement correspondant à la pilule à prendre
    const events = calendar.getEvents();
    events.forEach(event => {
        if (event.title === pilule.libelle + ' à prendre' && event.start.getTime() === date.getTime()) {
            event.remove();
        }
    });

    const heure = new Date().getHours().toString().padStart(2, '0') + ':' + new Date().getMinutes().toString().padStart(2, '0');

    const newEvent = {
        title: pilule.libelle + ' pris à ' + heure,
        start: date,
        allDay: true
    };
    calendar.addEvent(newEvent);

}