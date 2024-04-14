<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stuffstocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("stuff_id"); //untuk FK yang PK nya id auto increments
            $table->integer("total_avaiblable");
            $table->integer("total_defec");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stuffstocks');
    }
};
