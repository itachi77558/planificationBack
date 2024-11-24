<?php

// database/migrations/2024_11_24_create_scheduled_transfers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduled_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('sender_id');
            $table->string('recipient_phone');
            $table->decimal('amount', 10, 2);
            $table->timestamp('scheduled_date');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_transfers');
    }
};