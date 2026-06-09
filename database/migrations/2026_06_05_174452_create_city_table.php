<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city', function (Blueprint $table) {
            $table->increments('city_id');

            $table->unsignedInteger('province_id');

            $table->string('city_code', 10);
            $table->string('city_name', 50);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('province_id')
                ->references('province_id')
                ->on('province');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city');
    }
};