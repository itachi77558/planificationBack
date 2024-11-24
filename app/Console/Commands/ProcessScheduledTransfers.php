<?php

// app/Console/Commands/ProcessScheduledTransfers.php
namespace App\Console\Commands;

use App\Models\ScheduledTransfer;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledTransfers extends Command
{
    protected $signature = 'transfers:process';
    protected $description = 'Traite les transferts planifiés';

    public function handle(FirebaseService $firebaseService)
    {
        $pendingTransfers = ScheduledTransfer::where('status', 'pending')
            ->where('scheduled_date', '<=', Carbon::now())
            ->get();

        foreach ($pendingTransfers as $transfer) {
            try {
                $firebaseService->executeTransfer(
                    $transfer->sender_id,
                    $transfer->recipient_phone,
                    $transfer->amount
                );

                $transfer->update(['status' => 'completed']);
            } catch (\Exception $e) {
                Log::error("Erreur lors du traitement du transfert #{$transfer->id}: " . $e->getMessage());
                $transfer->update(['status' => 'failed']);
            }
        }
    }
}