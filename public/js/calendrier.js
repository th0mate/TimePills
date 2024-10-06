document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
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
            //TODO : ajouter un max au jour d'aujourd'hui dans le HTML pour la date DerniereReprise pour ne pas mettre cette date dans le futur !

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
                    //la date d'aujourd'hui sans les heures
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

        /*
        //si 'datesDernieresPrises' est vide, on ne fait rien
        if (pilule.datesPrises.length > 0) {
            //pour chaque date dans datesDernieresPrises, on ajoute un événement 'Pilule pris' dans le calendrier
            for (let date of pilule.datesPrises) {
                const event = {
                    title: 'Pilule pris',
                    start: date,
                    allDay: true
                };
                calendar.addEvent(event);
            }
        } else {
            //TODO : ne pas afficher les 'pris' si ils sont déjà présents en tant qu'événements sur le calendrier !
            //TODO : les 'pris' remplacent les 'à prendre' si ils sont présents en tant qu'événements sur le calendrier pour le même libellé et le même jour !
            //TODO : mettre l'heure de prise pour chaque jour où la pilule est prise
        }

         */


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

    // Récupère le calendrier
    const calendarEl = document.getElementById('calendar');
    const calendar = FullCalendar.getCalendar(calendarEl);

    // Supprime l'événement correspondant à la pilule à prendre
    const events = calendar.getEvents();
    events.forEach(event => {
        if (event.title === pilule.libelle + ' à prendre' && event.start.getTime() === date.getTime()) {
            event.remove();
        }
    });

    // Ajoute un nouvel événement indiquant que la pilule a été prise
    const newEvent = {
        title: pilule.libelle + ' pris',
        start: date,
        allDay: true
    };
    calendar.addEvent(newEvent);

}