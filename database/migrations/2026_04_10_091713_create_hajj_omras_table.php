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
        Schema::create('hajj_omras', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->enum('type', ['hajj', 'omra'])->default('omra');
            $table->string('formule',100);
            $table->date('dateDepartHO');
            $table->date('dateRetourHO');
            $table->integer('duree')->nullable();
            $table->string('typeChambre');
            $table->foreign('id')->references('id')->on('services')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hajj_omras');
    }
};
