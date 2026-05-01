<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hajj_omras', function (Blueprint $table) {
            $table->string('duree', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hajj_omras', function (Blueprint $table) {
            $table->string('duree', 255)->nullable()->change();
        });
    }
};