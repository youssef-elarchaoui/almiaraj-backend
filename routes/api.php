<?php

use App\Http\Controllers\AvisController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HajjOmraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VoyageController;


Route::get('/user', function () {
    return response()->json(['message' => 'Unauthenticated']);
})->name('user');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Admin routes
Route::prefix('admin')->group(function () {
    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'showAd']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    Route::put('/reservations/{id}/status', [ReservationController::class, 'updateStatus']);

    // Clients
    Route::get('/clients', [ClientController::class, 'adminIndex']);
    Route::get('/clients/{id}', [ClientController::class, 'adminShow']);
    Route::delete('/clients/{id}', [ClientController::class, 'adminDestroy']);

    // Avis
    Route::get('/avis', [AvisController::class, 'adminIndex']);
    Route::get('/avis/{id}', [AvisController::class, 'adminShow']);
    Route::delete('/avis/{id}', [AvisController::class, 'adminDestroy']);

    // Messages
    Route::get('/messages', [MessageController::class, 'adminIndex']);
    Route::get('/messages/{id}', [MessageController::class, 'adminShow']);
    Route::delete('/messages/{id}', [MessageController::class, 'adminDestroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reservations/voyage', [ReservationController::class, 'storeVoyage']);
    Route::post('/reservations/hotel', [ReservationController::class, 'storeHotel']);
    Route::post('/billets/reserver', [ReservationController::class, 'storeBillet']);
    Route::get('/my-reservations', [ReservationController::class, 'myReservations']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::get('/reservations/check-limits', [ReservationController::class, 'checkLimits']);
});

Route::post('/contact/message', [MessageController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-messages', [MessageController::class, 'myMessages']);
    Route::get('/client/messages/{id}', [MessageController::class, 'showForClient']);
    Route::post('/client/messages/{id}/reply', [MessageController::class, 'reply']);
});

Route::post('/services', [ServiceController::class, 'store']);

Route::middleware('auth:sanctum')->get('/client/profile', [ClientController::class, 'getProfile']);
Route::middleware('auth:sanctum')->put('/client/profile', [ClientController::class, 'update']);

Route::get('/destinationsCl', [DestinationController::class, 'indexCl']);
Route::get('/destinationsCl/{id}/services', [DestinationController::class, 'getServicesCl']);

Route::get('/billetsCl', [BilletController::class, 'indexCl']);
Route::get('/omraHajjCl', [HajjOmraController::class, 'indexCl']);
Route::get('/voyagesCl', [VoyageController::class, 'indexCl']);
Route::get('/hotelsCl', [HotelController::class, 'indexCl']);

Route::get('/omraHajjCl/{id}', [HajjOmraController::class, 'showCl']);
Route::get('/billetsCl/{id}', [BilletController::class, 'showCl']);
Route::get('/hotelsCl/{id}', [HotelController::class, 'showCl']);
Route::get('/voyagesCl/{id}', [VoyageController::class, 'showCl']);

Route::prefix('voyages')->group(function () {
    Route::get('/', [VoyageController::class, 'index']);
    Route::get('/{id}', [VoyageController::class, 'show']);
    Route::post('/', [VoyageController::class, 'store']);
    Route::put('/{id}', [VoyageController::class, 'update']);
    Route::delete('/{id}', [VoyageController::class, 'destroy']);
});
Route::prefix('hotels')->group(function () {
    Route::get('/', [HotelController::class, 'index']);
    Route::get('/{id}', [HotelController::class, 'show']);
    Route::post('/', [HotelController::class, 'store']);
    Route::put('/{id}', [HotelController::class, 'update']);
    Route::delete('/{id}', [HotelController::class, 'destroy']);
});
Route::prefix('hajj-omras')->group(function () {
    Route::get('/', [HajjOmraController::class, 'index']);
    Route::get('/{id}', [HajjOmraController::class, 'show']);
    Route::post('/', [HajjOmraController::class, 'store']);
    Route::put('/{id}', [HajjOmraController::class, 'update']);
    Route::delete('/{id}', [HajjOmraController::class, 'destroy']);
});
Route::prefix('billets')->group(function () {
    Route::get('/', [BilletController::class, 'index']);
    Route::get('/{id}', [BilletController::class, 'show']);
    Route::post('/', [BilletController::class, 'store']);
    Route::put('/{id}', [BilletController::class, 'update']);
    Route::delete('/{id}', [BilletController::class, 'destroy']);
});

Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/destinations/search', [VoyageController::class, 'searchDestinations']);
Route::post('/voyages', [VoyageController::class, 'store']);

Route::prefix('destinations')->group(function () {
    Route::get('/', [DestinationController::class, 'index']);
    Route::get('/{id}', [DestinationController::class, 'show']);
    Route::post('/', [DestinationController::class, 'store']);
    Route::put('/{id}', [DestinationController::class, 'update']);
    Route::delete('/{id}', [DestinationController::class, 'destroy']);
    Route::patch('/{id}/toggle-featured', [DestinationController::class, 'toggleFeatured']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword']);
});


require __DIR__ . '/auth.php';
