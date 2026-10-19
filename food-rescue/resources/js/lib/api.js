// Klien HTTP kecil untuk API FoodRescue (Laravel Sanctum, token Bearer).

const KUNCI_TOKEN = 'fr_token';
const KUNCI_USER = 'fr_user';

export const sesi = {
    get token() {
        return localStorage.getItem(KUNCI_TOKEN);
    },
    get user() {
        try {
            return JSON.parse(localStorage.getItem(KUNCI_USER));
        } catch {
            return null;
        }
    },
    simpan(token, user) {
        localStorage.setItem(KUNCI_TOKEN, token);
        localStorage.setItem(KUNCI_USER, JSON.stringify(user));
    },
    simpanUser(user) {
        localStorage.setItem(KUNCI_USER, JSON.stringify(user));
    },
    hapus() {
        localStorage.removeItem(KUNCI_TOKEN);
        localStorage.removeItem(KUNCI_USER);
    },
};

export class GalatApi extends Error {
    constructor(pesan, status, galat = null) {
        super(pesan);
        this.status = status;
        this.galat = galat; // objek { field: [pesan, ...] } dari validasi 422
    }
}

export async function api(path, { method = 'GET', body } = {}) {
    const headers = { Accept: 'application/json' };
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    if (sesi.token) headers.Authorization = `Bearer ${sesi.token}`;

    let res;
    try {
        res = await fetch(`/api${path}`, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });
    } catch {
        throw new GalatApi('Tidak dapat terhubung ke server. Periksa koneksi Anda.', 0);
    }

    let json = null;
    try {
        json = await res.json();
    } catch {
        /* respons bukan JSON */
    }

    if (res.status === 401 && sesi.token) {
        sesi.hapus();
        window.location.href = '/masuk';
    }

    if (!res.ok) {
        // `pesan` dari controller kita, `message` dari abort() / error bawaan Laravel
        throw new GalatApi(json?.pesan || json?.message || 'Terjadi kesalahan pada server.', res.status, json?.galat ?? null);
    }

    return json;
}

/** Arahkan ke /masuk bila belum login. Mengembalikan user yang tersimpan. */
export function wajibLogin() {
    if (!sesi.token || !sesi.user) {
        sesi.hapus();
        window.location.replace('/masuk');
        return null;
    }
    return sesi.user;
}

/** Tujuan setelah login: admin ke panel admin, peran lain ke dasbor. */
export const halamanUtama = (user) => (user?.role === 'admin' ? '/admin' : '/dasbor');

/** Arahkan ke halaman utama bila sudah login (untuk halaman masuk/daftar). */
export function wajibTamu() {
    if (sesi.token && sesi.user) {
        window.location.replace(halamanUtama(sesi.user));
        return false;
    }
    return true;
}
