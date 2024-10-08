function demanderNotification() {
    if (Notification.permission === 'granted') {
        alert('Vous avez déjà accepté les notifications.');
    } else if (Notification.permission === 'denied' || Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                afficherMessageFlash('Merci d\'avoir accepté les notifications.', 'success');
            } else {
                afficherMessageFlash('Vous avez refusé les notifications.', 'info');
            }
            document.querySelector('.veuxNotification').style.display = 'none';
        });
    }
}

if (Notification.permission === 'granted') {
    document.querySelector('.veuxNotification').style.display = 'none';
}