document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        buttonText: {
            today: 'Aujourd\'hui'
        },
        eventDidMount: function(info) {
            if (info.event.title === 'Pilule pris') {
                info.el.style.backgroundColor = '#258700';
            } else if (info.event.title === 'Pilule à prendre') {
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
    addEventsToCalendar(calendar);
});

/**
 * Ajoute des événements au calendrier, dans les trois prochains jours
 * 1 événement aujourd'hui 'Pilule pris', 1 événement demain 'Pilule à prendre', 1 événement après-demain 'Pilule à prendre'
 */
function addEventsToCalendar(calendar) {
    const events = [
        {
            title: 'Pilule pris',
            start: new Date(),
            allDay: true
        },
        {
            title: 'Pilule à prendre',
            start: new Date(new Date().setDate(new Date().getDate() + 1)),
            allDay: true
        },
        {
            title: 'Pilule à prendre',
            start: new Date(new Date().setDate(new Date().getDate() + 2)),
            allDay: true
        },
        {
            title: 'Pause',
            start: new Date(new Date().setDate(new Date().getDate() + 3)),
            allDay: true
        }
    ];

    events.forEach(event => calendar.addEvent(event));
}