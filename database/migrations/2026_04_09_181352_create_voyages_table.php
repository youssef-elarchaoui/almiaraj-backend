<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('voyages', function (Blueprint $table){
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->date('dateDepartV');
            $table->string('duree')->nullable();
            $table->date('dateRetourV');
            $table->text('programme');
            $table->foreign('id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voyages');
    }
};