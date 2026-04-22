const CACHE_NAME = 'medicature-v1';
const STATIC_ASSETS = [
    '/medicure/assets/css/style.css',
    '/medicure/assets/js/main.js',
    '/medicure/login.php',
];

// Install: cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// Fetch: serve from cache first, fallback to network
self.addEventListener('fetch', event => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // Skip API calls - always fetch those fresh
    if (event.request.url.includes('/api/')) return;

    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).then(networkResponse => {
                // Cache CSS and JS files
                if (event.request.url.match(/\.(css|js|png|jpg|ico)$/)) {
                    const clone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return networkResponse;
            }).catch(() => {
                // Offline fallback for HTML pages
                if (event.request.headers.get('accept').includes('text/html')) {
                    return new Response(
                        '<h1>Medicature</h1><p>You are offline. Please check your connection.</p>',
                        { headers: { 'Content-Type': 'text/html' } }
                    );
                }
            });
        })
    );
});

// Background sync for medication reminders (future enhancement)
self.addEventListener('push', event => {
    const data = event.data ? event.data.json() : { title: 'Medicature', body: 'Time to take your medication!' };
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/medicure/assets/icons/icon-192.png',
            badge: '/medicure/assets/icons/icon-192.png',
        })
    );
});
