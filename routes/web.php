<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/test-db', function() {
    try {
        DB::connection()->getPdo();
        return "✅ Database connection successful! Database: " . DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "❌ Database connection failed: " . $e->getMessage();
    }
});