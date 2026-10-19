// Titik masuk JavaScript. Setiap halaman memuat modulnya sendiri (lazy) sesuai <body data-page="...">.

const halaman = {
    beranda: () => import('./pages/beranda'),
    masuk: () => import('./pages/auth'),
    daftar: () => import('./pages/auth'),
    dasbor: () => import('./pages/dasbor'),
    admin: () => import('./pages/admin'),
};

const muat = halaman[document.body.dataset.page];

if (muat) {
    muat().then((modul) => modul.default?.());
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
