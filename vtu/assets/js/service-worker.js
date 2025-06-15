// Service Worker for VTU Platform (PWA)

const CACHE_NAME = 'vtu-platform-v1';
const OFFLINE_URL = '/offline.html';
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/offline.html',
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/assets/images/logo.png',
  '/assets/images/icon-192.png',
  '/assets/images/icon-512.png',
  '/assets/images/bg-pattern.png',
  '/manifest.json'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(ASSETS_TO_CACHE);
      })
  );
});

// Fetch Event Handling
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;
  
  // Handle API requests differently
  if (event.request.url.includes('/api/')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Cache successful API responses
          if (response.status === 200) {
            const cacheCopy = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => cache.put(event.request, cacheCopy));
          }
          return response;
        })
        .catch(() => {
          // Return cached API response if available
          return caches.match(event.request);
        })
    );
    return;
  }
  
  // For non-API requests
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful responses
        if (response.status === 200) {
          const cacheCopy = response.clone();
          caches.open(CACHE_NAME)
            .then(cache => cache.put(event.request, cacheCopy));
        }
        return response;
      })
      .catch(() => {
        // Return offline page for navigation requests
        if (event.request.mode === 'navigate') {
          return caches.match(OFFLINE_URL);
        }
        // Return cached assets
        return caches.match(event.request);
      })
  );
});

// Clean up old caches
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background Sync (for failed requests)
self.addEventListener('sync', event => {
  if (event.tag === 'syncTransactions') {
    event.waitUntil(syncPendingTransactions());
  }
});

async function syncPendingTransactions() {
  const pendingTxns = await getPendingTransactions();
  
  for (const txn of pendingTxns) {
    try {
      await retryTransaction(txn);
      await markTransactionAsSynced(txn.id);
    } catch (error) {
      console.error('Failed to sync transaction:', txn.id, error);
    }
  }
}

function getPendingTransactions() {
  // In a real implementation, this would use IndexedDB
  return Promise.resolve([]);
}

function retryTransaction(txn) {
  // Implementation would retry the transaction
  return Promise.resolve();
}

function markTransactionAsSynced(id) {
  // Implementation would update transaction status
  return Promise.resolve();
}