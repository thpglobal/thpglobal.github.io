cacheName='pwa1';
const URLS = [
  './',
  './functions.js',
  './index.html',
  "./logo150.png",
  "./logo512.png",
  "./manifest.json",
  './style.css',
  './sw.js'
];

self.addEventListener('install', (e) => {
  e.waitUntil((async () => {
    const cache = await caches.open(cacheName);
    await cache.addAll(URLS);
  })());
});

self.addEventListener('fetch', function (e) {
  e.respondWith(
      fetch(e.request).catch(function() {
          return caches.match(e.request,{'ignoreSearch':true})
      })
  )
})

self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((keyList) => {
    Promise.all(keyList.map((key) => {
      if (key === cacheName) { return; }
      caches.delete(key);
    }))
  }));
});
