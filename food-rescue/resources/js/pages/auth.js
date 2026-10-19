// Halaman masuk & daftar: kirim formulir ke API, simpan token Sanctum.

import { api, sesi, wajibTamu, halamanUtama, GalatApi } from '../lib/api';
import { setMemuat, tampilkanGalatField } from '../lib/ui';

function tampilkanAlert(pesan) {
    const el = document.getElementById('form-alert');
    if (!el) return;
    el.textContent = pesan ?? '';
    el.classList.toggle('hidden', !pesan);
}

async function login(email, password) {
    const res = await api('/auth/login', { method: 'POST', body: { email, password } });
    sesi.simpan(res.data.token, res.data.user);
    window.location.href = halamanUtama(res.data.user);
}

export default function auth() {
    if (!wajibTamu()) return;

    // Tombol tampil/sembunyi kata sandi
    document.querySelectorAll('[data-toggle-password]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            const input = document.getElementById(tombol.dataset.togglePassword);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    const formMasuk = document.getElementById('form-masuk');
    const formDaftar = document.getElementById('form-daftar');

    formMasuk?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tombol = formMasuk.querySelector('[data-submit]');
        tampilkanAlert(null);
        tampilkanGalatField(formMasuk, null);
        setMemuat(tombol, true, 'Memeriksa…');

        try {
            await login(formMasuk.email.value.trim(), formMasuk.password.value);
        } catch (err) {
            tampilkanAlert(err.message);
            if (err instanceof GalatApi && err.galat) tampilkanGalatField(formMasuk, err.galat);
            setMemuat(tombol, false);
        }
    });

    formDaftar?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tombol = formDaftar.querySelector('[data-submit]');
        tampilkanAlert(null);
        tampilkanGalatField(formDaftar, null);
        setMemuat(tombol, true, 'Membuat akun…');

        const data = Object.fromEntries(new FormData(formDaftar));
        data.email = data.email.trim();
        if (!data.alamat) delete data.alamat;

        try {
            await api('/auth/register', { method: 'POST', body: data });
            // Setelah terdaftar, langsung masuk otomatis
            await login(data.email, data.password);
        } catch (err) {
            tampilkanAlert(err.message);
            if (err instanceof GalatApi && err.galat) tampilkanGalatField(formDaftar, err.galat);
            setMemuat(tombol, false);
        }
    });
}
