const CACHE_NAME = 'morpheus-cache-v1';
const STATIC_ASSETS = [
  '/',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/manifest.webmanifest',
];

// Cache dinâmico com nome separado
const DYNAMIC_CACHE = 'morpheus-dynamic-v1';

// Instalação: cache básico estático
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting(); // ativa imediatamente
});

// Ativação: remove caches antigos
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME && key !== DYNAMIC_CACHE)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// Intercepta todas as requisições
self.addEventListener('fetch', event => {
  const { request } = event;

  // Cache-first para arquivos estáticos
  if (STATIC_ASSETS.includes(new URL(request.url).pathname)) {
    event.respondWith(
      caches.match(request).then(cached => cached || fetch(request))
    );
    return;
  }

  // Network-first para o restante (scripts do Vite com hash, API etc.)
  event.respondWith(
    fetch(request)
      .then(response => {
        return caches.open(DYNAMIC_CACHE).then(cache => {
          cache.put(request, response.clone());
          return response;
        });
      })
      .catch(() => caches.match(request)) // fallback em caso de erro offline
  );
});
