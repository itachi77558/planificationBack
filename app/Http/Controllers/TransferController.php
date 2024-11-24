<?php

// app/Http/Controllers/TransferController.php
namespace App\Http\Controllers;

use App\Models\ScheduledTransfer;
use Illuminate\Http\Request;
use App\Console\Commands\ProcessScheduledTransfers;
use App\Services\FirebaseService;

class TransferController extends Controller
{
    public function scheduleTransfer(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|string',
            'recipient_phone' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'scheduled_date' => 'required|date|after:now',
        ]);

        $transfer = ScheduledTransfer::create($validated);

        return response()->json([
            'message' => 'Transfert planifié avec succès',
            'transfer' => $transfer
        ]);
    }

    public function processTransfers()
    {
        try {
            $command = app()->make(ProcessScheduledTransfers::class);
            $command->handle(app()->make(FirebaseService::class));
            
            return response()->json(['message' => 'Transferts traités avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}