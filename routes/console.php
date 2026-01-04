<?php

use App\Models\LockerSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| AUTO RELEASE LOCKER (TEST MODE)
|--------------------------------------------------------------------------
| - Cek tiap 1 menit
| - Jika 3 menit KOSONG sejak booking → auto release
| - Jika barang sudah masuk lalu diambil → auto release
| - Semua event di-log
*/

Schedule::call(function () {

    Log::info('=== AUTO RELEASE SCHEDULER START ===');

    $sessions = LockerSession::whereIn('status', ['active', 'filled'])->get();

    foreach ($sessions as $session) {

        try {
            // HIT API IOT
            $response = Http::timeout(5)->get('http://127.0.0.1:2200/api/locker');

            if (! $response->ok()) {
                Log::error('IOT API ERROR', [
                    'session_id' => $session->id,
                    'http_status' => $response->status(),
                ]);
                continue;
            }

            $sensor = $response->json('sensor1');

            // HITUNG MENIT (ANTI MINUS)
            $minutesPassed = $session->created_at->diffInMinutes(now());

            Log::info('AUTO-RELEASE CHECK', [
                'session_id' => $session->id,
                'locker_id'  => $session->locker_id,
                'status'     => $session->status,
                'sensor'     => $sensor,
                'minutes'    => $minutesPassed,
            ]);

            /*
            |--------------------------------------------------------------------------
            | CASE 1
            | Baru booking, 3 menit masih kosong → AUTO RELEASE
            |--------------------------------------------------------------------------
            */
            if (
                $session->status === 'active' &&
                $sensor === 'KOSONG' &&
                $minutesPassed >= 3
            ) {
                autoRelease($session, 'EMPTY_MORE_THAN_3_MIN');
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CASE 2
            | Barang masuk → tandai filled
            |--------------------------------------------------------------------------
            */
            if (
                $session->status === 'active' &&
                $sensor === 'ADA_BARANG'
            ) {
                $session->update([
                    'status' => 'filled',
                ]);

                Log::info('LOCKER FILLED', [
                    'session_id' => $session->id,
                ]);

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CASE 3
            | Barang sudah diambil → AUTO RELEASE
            |--------------------------------------------------------------------------
            */
            if (
                $session->status === 'filled' &&
                $sensor === 'KOSONG'
            ) {
                autoRelease($session, 'ITEM_TAKEN');
                continue;
            }

        } catch (\Throwable $e) {
            Log::error('AUTO RELEASE EXCEPTION', [
                'session_id' => $session->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    Log::info('=== AUTO RELEASE SCHEDULER END ===');

})->everyMinute();

/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/
function autoRelease(LockerSession $session, string $reason): void
{
    DB::transaction(function () use ($session, $reason) {

        $session->update([
            'status' => 'expired',
        ]);

        $session->locker()->update([
            'status' => 'available',
        ]);

        Log::warning('LOCKER AUTO RELEASED', [
            'session_id' => $session->id,
            'locker_id'  => $session->locker_id,
            'reason'     => $reason,
        ]);
    });
}
