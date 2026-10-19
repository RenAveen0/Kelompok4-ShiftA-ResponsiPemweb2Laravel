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
