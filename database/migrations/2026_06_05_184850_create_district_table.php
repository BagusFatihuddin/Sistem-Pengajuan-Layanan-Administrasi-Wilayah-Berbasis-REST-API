<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('district', function (Blueprint $table) {

            $table->increments('district_id');

            $table->unsignedInteger('city_id');

            $table->string('district_code', 10);
            $table->string('district_name', 50);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')
                ->references('city_id')
                ->on('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district');
    }
};