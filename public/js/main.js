// public/main.js

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(lienServiceWorker)
        .then(function(registration) {
            console.log('Service Worker registered with scope:', registration.scope);
        })
        .catch(function(error) {
            console.log('Service Worker registration failed:', error);
        });
}