<?php

namespace App\Observers;

use App\Models\LockerItem;
use App\Models\Notification;

class LockerItemObserver
{
    /**
     * Trigger saat locker_item di-update
     */
    public function updated(LockerItem $item): void
    {
        // -------- Notif saat barang masuk ke locker --------
        if ($item->wasChanged('added_at') && $item->added_at !== null) {
            $itemName = $item->item_name ?? 'Barang';
            $userId   = optional($item->session)->user_id;

            if ($userId) {
                Notification::create([
                    'user_id'        => $userId,
                    'locker_item_id' => $item->id,
                    'title'          => "{$itemName} telah masuk ke locker",
                    'is_read'        => false,
                ]);
            }
        }

        // -------- Notif saat barang sudah diambil --------
        if ($item->wasChanged('taken_at') && $item->taken_at !== null) {
            $userId = optional($item->session)->user_id;
            $takenByName = optional($item->session->assignedTaker)->name ?? 'Unknown';

            if ($userId) {
                Notification::create([
                    'user_id'        => $userId,
                    'locker_item_id' => $item->id,
                    'title'          => "Barang telah diambil oleh {$takenByName}",
                    'type'           => 'item_taken',
                    'data'           => [
                        'taken_by' => $takenByName,
                        'taken_at' => $item->taken_at,
                    ],
                    'is_read'        => false,
                ]);
            }
        }
    }
}
