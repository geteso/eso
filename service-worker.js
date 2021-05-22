// service-worker.js
// Tells a client what should be cached in case the site goes offline.
  
// Setting up the offline page in our cache, and opening a new cache.
self.addEventListener('install', function(event) {
    var offlinePage = new Request('offline.html');
    event.waitUntil(
        fetch(offlinePage).then(function(response) {
            return caches.open('offline').then(function(cache) {
                console.log('Cached offline page during install: '+ response.url);
                return cache.put(offlinePage, response);
            });
        }));
});
  
// If the fetch fails, it'll show the offline page.
self.addEventListener('fetch', function(event) {
    event.respondWith(
        fetch(event.request).catch(function(error) {
            console.error('Network request failed. Serving offline page: '+ error );
            return caches.open('offline').then(function(cache) {
                return cache.match('offline.html');
            });
        }
    ));
});
  
// Adding an event that can be fired, which updates the offline page.
self.addEventListener('refreshOffline', function(response) {
    return caches.open('offline').then(function(cache) {
        console.log('Offline page updated from refreshOffline event: '+ response.url);
        return cache.put(offlinePage, response);
    });
});
