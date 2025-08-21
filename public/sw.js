self.addEventListener("install", event => {
    console.log("Service Worker installed");
    event.waitUntil(
        caches.open("wasit-cache").then(cache => {
            return cache.addAll([
                "/",
                "/css/app.css",
                "/js/app.js"
            ]);
        })
    );
});

self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
