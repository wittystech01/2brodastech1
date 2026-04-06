// GadgetZone - Service Worker (PWA)

const CACHE_NAME = 'gadgetzone-v1';
const OFFLINE_URL = '/2brodastech1/offline.php';

const PRECACHE_ASSETS = [
    '/2brodastech1/',
    '/2brodastech1/index.php',
    '/2brodastech1/shop.php',
    '/2brodastech1/cart.php',
    '/2brodastech1/offline.php',
    '/2brodastech1/css/style.css',
    '/2brodastech1/css/mobile.css',
    '/2brodastech1/css/responsive.css',
    '/2brodastech1/js/main.js',
    '/2brodastech1/js/cart.js',
    '/2brodastech1/js/product.js',
    '/2brodastech1/manifest.json',
    '/2brodastech1/assets/icons/icon-192x192.png',
    '/2brodastech1/assets/icons/icon-512x512.png'
];

// ============================================================
// INSTALL: Cache core assets
// ============================================================
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// ============================================================
// ACTIVATE: Clean up old caches
// ============================================================
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// ============================================================
// FETCH: Network-first for API/PHP, Cache-first for assets
// ============================================================
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin requests
    if (request.method !== 'GET' || url.origin !== location.origin) return;

    // Skip admin and API routes from caching
    if (url.pathname.includes('/admin/') || url.pathname.includes('/api/') || url.pathname.includes('/ajax/')) {
        return;
    }

    // For PHP pages: Network first, fall back to cache, then offline page
    if (url.pathname.endsWith('.php') || url.pathname.endsWith('/')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(request).then(cached =>
                        cached || caches.match(OFFLINE_URL)
                    )
                )
        );
        return;
    }

    // For static assets (CSS, JS, images): Cache first, then network
    event.respondWith(
        caches.match(request).then(cached => {
            if (cached) return cached;
            return fetch(request).then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                }
                return response;
            });
        })
    );
});

// ============================================================
// BACKGROUND SYNC (for deferred cart actions)
// ============================================================
self.addEventListener('sync', event => {
    if (event.tag === 'sync-cart') {
        event.waitUntil(syncCart());
    }
});

async function syncCart() {
    try {
        const db = await openCartDB();
        const pendingActions = await db.getAll('pending_actions');
        for (const action of pendingActions) {
            await fetch('/2brodastech1/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(action)
            });
        }
        await db.clear('pending_actions');
    } catch (e) {
        // Sync will retry
    }
}

function openCartDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('gadgetzone-cart', 1);
        req.onupgradeneeded = e => e.target.result.createObjectStore('pending_actions', { autoIncrement: true });
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = reject;
    });
}

// ============================================================
// PUSH NOTIFICATIONS
// ============================================================
self.addEventListener('push', event => {
    if (!event.data) return;
    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title || 'GadgetZone', {
            body: data.body || 'You have a new notification.',
            icon: '/2brodastech1/assets/icons/icon-192x192.png',
            badge: '/2brodastech1/assets/icons/icon-72x72.png',
            data: { url: data.url || '/2brodastech1/' },
            actions: data.actions || []
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url || '/2brodastech1/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url === url && 'focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
