<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request', function (Blueprint $table) {
            $table->increments('service_request_id');
            $table->string('request_number', 30)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('service_type_id');
            $table->unsignedInteger('district_id');
            $table->string('applicant_name', 100);
            $table->string('applicant_nik', 16);
            $table->string('applicant_phone', 20);
            $table->text('applicant_address');
            $table->text('purpose');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('service_type_id')
                ->references('service_type_id')
                ->on('service_type');

            $table->foreign('district_id')
                ->references('district_id')
                ->on('district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request');
    }
};
