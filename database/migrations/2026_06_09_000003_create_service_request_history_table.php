<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_history', function (Blueprint $table) {
            $table->increments('history_id');
            $table->unsignedInteger('service_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('previous_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->text('notes')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();

            $table->foreign('service_request_id')
                ->references('service_request_id')
                ->on('service_request');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_history');
    }
};
