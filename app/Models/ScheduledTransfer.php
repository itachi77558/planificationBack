<?php

// app/Models/ScheduledTransfer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTransfer extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_phone',
        'amount',
        'scheduled_date',
        'status', // 'pending', 'completed', 'failed'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'amount' => 'float',
    ];
}