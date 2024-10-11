// public/service-worker.js

self.addEventListener('push', function(event) {
    console.log('Push event received:', event);
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notification click event:', event);
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});