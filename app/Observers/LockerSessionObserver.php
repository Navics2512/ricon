<?php

namespace App\Observers;

use App\Models\LockerSession;
use App\Models\Notification;

class LockerSessionObserver
{
    /**
     * Handle the LockerSession "created" event.
     */
     public function created(LockerSession $session)
    {
        // Trigger notif ketika session baru dibuat (booking)
    }

    /**
     * Handle the LockerSession "updated" event.
     */
    public function updated(LockerSession $session)
    {
         Notification::create([
            'user_id' => $session->user_id,
            'locker_item_id' => null, // belum ada item
            'title' => "Locker {$session->locker?->id} berhasil dibooking!",
            'is_read' => false,
        ]);
        if ($session->wasChanged('ended_at') && $session->ended_at) {
            Notification::create([
                'user_id' => $session->user_id,
                'title' => 'Barang telah diambil dari locker',
            ]);
        }

        // SESSION EXPIRED
        if ($session->wasChanged('status') && $session->status === 'expired') {
            Notification::create([
                'user_id' => $session->user_id,
                'title' => 'Booking locker telah expired',
            ]);
        }
    }

    /**
     * Handle the LockerSession "deleted" event.
     */
    public function deleted(LockerSession $lockerSession): void
    {
        //
    }

    /**
     * Handle the LockerSession "restored" event.
     */
    public function restored(LockerSession $lockerSession): void
    {
        //
    }

    /**
     * Handle the LockerSession "force deleted" event.
     */
    public function forceDeleted(LockerSession $lockerSession): void
    {
        //
    }
}
