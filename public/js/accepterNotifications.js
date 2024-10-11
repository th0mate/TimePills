async function demanderNotification() {
    if (Notification.permission === 'granted') {
        alert('Vous avez déjà accepté les notifications.');
    } else if (Notification.permission === 'denied' || Notification.permission === 'default') {
        Notification.requestPermission().then(async permission => {
            if (permission === 'granted') {
                afficherMessageFlash('Merci d\'avoir accepté les notifications.', 'success');
                let URL = Routing.generate('changerNotification', {"veutNotification": true});
                const response = await fetch(URL, {method: "POST"});

                OneSignal.push(async function () {
                    const userId = await OneSignal.User.PushSubscription.id
                    console.log('OneSignal User ID:', userId);
                    if (userId) {
                        let URL2 = Routing.generate('enregistrer_one_signal_id', {"oneSignalId": userId});
                        await fetch(URL2, {method: "POST"});
                    }
                });
            } else {
                afficherMessageFlash('Vous avez refusé les notifications.', 'info');
                let URL = Routing.generate('changerNotification', {"veutNotification": false});
                const response = await fetch(URL, {method: "POST"});
            }
            document.querySelector('.veuxNotification').style.display = 'none';
        });
    }
}

if (Notification.permission === 'granted') {
    document.querySelector('.veuxNotification').style.display = 'none';
}

async function test() {
    OneSignal.push(async function () {
        const userId = await OneSignal.User.PushSubscription.id
        console.log('OneSignal User ID:', userId);
    });
}