<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('billets', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->enum('typeBi', ['aller_simple', 'aller_retour'])->default('aller_retour');
            $table->string('villeDepartBi', 100);
            $table->string('villeArriveeBi', 100);
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->date('dateDepartBi');
            $table->date('dateRetourBi')->nullable();
            $table->foreign('id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billets');
    }
};