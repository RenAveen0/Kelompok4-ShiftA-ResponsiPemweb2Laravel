<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonasiMakanan;
use App\Models\KlaimDonasi;
use App\Models\UlasanDonasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DasborController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $totalPorsiDiselamatkan = KlaimDonasi::where('status', 'selesai')->sum('jumlah_porsi');
        $totalKlaimSelesai      = KlaimDonasi::where('status', 'selesai')->count();
        $donasiAktif             = DonasiMakanan::where('status', 'tersedia')->count();
        $rataRataRating         = round((float) UlasanDonasi::avg('rating'), 2);

        // DATE_FORMAT hanya ada di MySQL/MariaDB; ekspresi disesuaikan dengan driver database aktif
        $formatBulan = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql'  => "to_char(created_at, 'YYYY-MM')",
            default  => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $rekapBulanan = KlaimDonasi::where('status', 'selesai')
            ->selectRaw("{$formatBulan} as bulan, SUM(jumlah_porsi) as total_porsi")
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();

        return response()->json([
            'sukses' => true,
            'data'   => [
                'porsi_diselamatkan' => $totalPorsiDiselamatkan,
                'klaim_selesai'      => $totalKlaimSelesai,
                'donasi_aktif'       => $donasiAktif,
                'rata_rata_rating'   => $rataRataRating,
                'grafik_bulanan'     => $rekapBulanan,
            ]
        ]);
    }
}