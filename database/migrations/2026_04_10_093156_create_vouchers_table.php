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
        Schema::disableForeignKeyConstraints();
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('codeV')->unique();
            $table->date('dateP');
            $table->string('urlPDF');
            $table->date('dateExpiration');
            $table->foreign('id')
                ->references('id')
                ->on('reservations')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
