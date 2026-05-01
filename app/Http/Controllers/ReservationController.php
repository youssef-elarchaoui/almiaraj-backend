<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Client;
use App\Models\Passager;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Vérifier les limites de réservation pour un client
     */
    private function checkReservationLimits($clientId)
    {
        // Limite de réservations par jour
        $maxPerDay = 4;

        // Compter les réservations aujourd'hui
        $todayCount = Reservation::where('client_id', $clientId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($todayCount >= $maxPerDay) {
            return [
                'allowed' => false,
                'message' => "Vous avez atteint la limite de {$maxPerDay} réservations par jour. Veuillez réessayer demain.",
                'remaining_today' => 0,
                'used_today' => $todayCount,
                'max_per_day' => $maxPerDay
            ];
        }

        // Limite de réservations en attente
        $maxPending = 3;
        $pendingCount = Reservation::where('client_id', $clientId)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount >= $maxPending) {
            return [
                'allowed' => false,
                'message' => "Vous avez trop de réservations en attente ({$pendingCount}/{$maxPending}). Veuillez finaliser ou annuler certaines réservations.",
                'pending_count' => $pendingCount,
                'max_pending' => $maxPending
            ];
        }

        return [
            'allowed' => true,
            'remaining_today' => $maxPerDay - $todayCount,
            'used_today' => $todayCount,
            'max_per_day' => $maxPerDay,
            'pending_count' => $pendingCount,
            'max_pending' => $maxPending
        ];
    }

    /**
     * Générer une référence unique
     */

    public function index()
    {
        try {
            $reservations = Reservation::with(['client', 'service'])->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des réservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->status = $request->status;
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }
    public function showAd($id)
    {
        try {
            $reservation = Reservation::with(['client', 'service'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée'
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Réservation supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    private function generateReference()
    {
        do {
            $reference = 'RES-' . strtoupper(uniqid());
        } while (Reservation::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Créer une réservation pour un voyage
     */
    public function storeVoyage(Request $request)
    {
        Log::info('Réservation voyage reçue', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'client_principal.nom' => 'required|string|max:50',
            'client_principal.prenom' => 'required|string|max:50',
            'client_principal.email' => 'required|email|max:100',
            'client_principal.telephone' => 'required|string|max:20',
            'client_principal.cin' => 'nullable|string|min:6|max:20',
            'reservation.nb_personnes' => 'required|integer|min:1',
            'reservation.prix_total' => 'required|numeric|min:0',
            'reservation.prix_unitaire' => 'required|numeric|min:0',
            'reservation.demandes_speciales' => 'nullable|string',
            'passagers' => 'nullable|array',
            'passagers.*.nom' => 'required_if:passagers,not_empty|string|max:50',
            'passagers.*.prenom' => 'required_if:passagers,not_empty|string|max:50',
            'passagers.*.cin' => 'nullable|string|max:20',
            'passagers.*.type_passager' => 'required_if:passagers,not_empty|in:adulte,enfant,nourrisson',
        ]);

        if ($validator->fails()) {
            Log::error('Validation échouée', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            DB::beginTransaction();

            $client = Client::where('id', $user->id)->first();

            if (!$client) {
                $client = Client::create([
                    'id' => $user->id,
                    'nomCl' => $validated['client_principal']['nom'],
                    'prenomCl' => $validated['client_principal']['prenom'],
                    'email' => $validated['client_principal']['email'],
                    'numTelCl' => $validated['client_principal']['telephone'],
                    'cin' => $validated['client_principal']['cin'] ?? null,
                    'natCl' => 'maroc',
                    'dateInscription' => now(),
                ]);
            }

            // ✅ Vérifier les limites AVANT de créer la réservation
            $limits = $this->checkReservationLimits($client->id);
            if (!$limits['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $limits['message'],
                    'limits' => $limits
                ], 429);
            }

            $reservation = Reservation::create([
                'service_id' => $validated['service_id'],
                'client_id' => $client->id,
                'nbPers' => $validated['reservation']['nb_personnes'],
                'prixUnitaire' => $validated['reservation']['prix_unitaire'],
                'prixTotal' => $validated['reservation']['prix_total'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'voucher_generated' => false,
                'reference' => $this->generateReference(),
            ]);

            Log::info('Réservation voyage créée', ['reservation_id' => $reservation->id]);

            if (!empty($validated['passagers'])) {
                foreach ($validated['passagers'] as $passagerData) {
                    Passager::create([
                        'reservation_id' => $reservation->id,
                        'nomPas' => $passagerData['nom'],
                        'prenomPas' => $passagerData['prenom'],
                        'cinPas' => $passagerData['cin'] ?? null,
                        'type_passager' => $passagerData['type_passager'] ?? 'adulte',
                    ]);
                }
                Log::info('Passagers voyage créés', ['count' => count($validated['passagers'])]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réservation voyage créée avec succès',
                'reservation' => $reservation->load('passagers', 'service'),
                'limits' => $limits
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création réservation voyage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une réservation pour un billet
     */
    public function storeBillet(Request $request)
    {
        Log::info('Réservation billet reçue', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'client_principal.nom' => 'required|string|max:50',
            'client_principal.prenom' => 'required|string|max:50',
            'client_principal.email' => 'required|email|max:100',
            'client_principal.telephone' => 'required|string|max:20',
            'client_principal.passport' => 'nullable|string|min:6|max:20',
            'reservation.nb_personnes' => 'required|integer|min:1',
            'reservation.prix_total' => 'required|numeric|min:0',
            'reservation.prix_unitaire' => 'required|numeric|min:0',
            'reservation.demandes_speciales' => 'nullable|string',
            'passagers' => 'nullable|array',
            'passagers.*.nom' => 'required_if:passagers,not_empty|string|max:50',
            'passagers.*.prenom' => 'required_if:passagers,not_empty|string|max:50',
            'passagers.*.passport' => 'nullable|string|max:20',
            'passagers.*.type_passager' => 'required_if:passagers,not_empty|in:adulte,enfant,nourrisson',
        ]);

        if ($validator->fails()) {
            Log::error('Validation échouée billet', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            DB::beginTransaction();

            $client = Client::where('id', $user->id)->first();

            if (!$client) {
                $client = Client::create([
                    'id' => $user->id,
                    'nomCl' => $validated['client_principal']['nom'],
                    'prenomCl' => $validated['client_principal']['prenom'],
                    'email' => $validated['client_principal']['email'],
                    'numTelCl' => $validated['client_principal']['telephone'],
                    'passport' => $validated['client_principal']['passport'] ?? null,
                    'natCl' => 'maroc',
                    'dateInscription' => now(),
                ]);
            }

            // ✅ Vérifier les limites AVANT de créer la réservation
            $limits = $this->checkReservationLimits($client->id);
            if (!$limits['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $limits['message'],
                    'limits' => $limits
                ], 429);
            }

            $prixUnitaire = $validated['reservation']['prix_unitaire'];
            $prixTotal = $validated['reservation']['prix_total'];
            $reference = 'RES-BIL-' . strtoupper(uniqid());

            Log::info('💰 Insertion reservation billet:', [
                'service_id' => $validated['service_id'],
                'client_id' => $client->id,
                'nbPers' => $validated['reservation']['nb_personnes'],
                'prixUnitaire' => $prixUnitaire,
                'prixTotal' => $prixTotal,
                'reference' => $reference
            ]);

            $reservationId = DB::table('reservations')->insertGetId([
                'service_id' => $validated['service_id'],
                'client_id' => $client->id,
                'nbPers' => $validated['reservation']['nb_personnes'],
                'prixUnitaire' => $prixUnitaire,
                'prixTotal' => $prixTotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'voucher_generated' => false,
                'reference' => $reference,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $reservation = Reservation::find($reservationId);

            if (!empty($validated['passagers'])) {
                foreach ($validated['passagers'] as $passagerData) {
                    DB::table('passagers')->insert([
                        'reservation_id' => $reservationId,
                        'nomPas' => $passagerData['nom'],
                        'prenomPas' => $passagerData['prenom'],
                        'passportPas' => $passagerData['passport'] ?? null,
                        'type_passager' => $passagerData['type_passager'] ?? 'adulte',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                Log::info('Passagers billet créés', ['count' => count($validated['passagers'])]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réservation billet créée avec succès',
                'reservation' => $reservation->load('passagers', 'service'),
                'limits' => $limits
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création réservation billet', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une réservation pour un hôtel
     */
    public function storeHotel(Request $request)
    {
        Log::info('Réservation hôtel reçue', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'client_principal.nom' => 'required|string|max:50',
            'client_principal.prenom' => 'required|string|max:50',
            'client_principal.email' => 'required|email|max:100',
            'client_principal.telephone' => 'required|string|max:20',
            'client_principal.cin' => 'nullable|string|min:6|max:20',
            'reservation.check_in' => 'required|date|after_or_equal:today',
            'reservation.check_out' => 'required|date|after:reservation.check_in',
            'reservation.type_chambre' => 'required|string|max:50',
            'reservation.nb_personnes' => 'required|integer|min:1',
            'reservation.prix_total' => 'required|numeric|min:0',
            'reservation.prix_unitaire' => 'required|numeric|min:0',
            'reservation.demandes_speciales' => 'nullable|string',
            'passagers' => 'nullable|array',
            'passagers.*.nom' => 'required_if:passagers,not_empty|string|max:50',
            'passagers.*.prenom' => 'required_if:passagers,not_empty|string|max:50',
            'passagers.*.cin' => 'nullable|string|max:20',
            'passagers.*.type_passager' => 'required_if:passagers,not_empty|in:adulte,enfant,nourrisson',
        ]);

        if ($validator->fails()) {
            Log::error('Validation échouée hôtel', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            DB::beginTransaction();

            $client = Client::where('id', $user->id)->first();

            if (!$client) {
                $client = Client::create([
                    'id' => $user->id,
                    'nomCl' => $validated['client_principal']['nom'],
                    'prenomCl' => $validated['client_principal']['prenom'],
                    'email' => $validated['client_principal']['email'],
                    'numTelCl' => $validated['client_principal']['telephone'],
                    'cin' => $validated['client_principal']['cin'] ?? null,
                    'natCl' => 'maroc',
                    'dateInscription' => now(),
                ]);
            }

            // ✅ Vérifier les limites AVANT de créer la réservation
            $limits = $this->checkReservationLimits($client->id);
            if (!$limits['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $limits['message'],
                    'limits' => $limits
                ], 429);
            }

            $reservationId = DB::table('reservations')->insertGetId([
                'service_id' => $validated['service_id'],
                'client_id' => $client->id,
                'nbPers' => $validated['reservation']['nb_personnes'],
                'prixUnitaire' => $validated['reservation']['prix_unitaire'],
                'prixTotal' => $validated['reservation']['prix_total'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'voucher_generated' => false,
                'reference' => $this->generateReference(),
                'check_in' => $validated['reservation']['check_in'],
                'check_out' => $validated['reservation']['check_out'],
                'type_chambre' => $validated['reservation']['type_chambre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $reservation = Reservation::find($reservationId);

            Log::info('Réservation hôtel créée', ['reservation_id' => $reservationId, 'prixTotal' => $validated['reservation']['prix_total']]);

            if (!empty($validated['passagers'])) {
                foreach ($validated['passagers'] as $passagerData) {
                    Passager::create([
                        'reservation_id' => $reservationId,
                        'nomPas' => $passagerData['nom'],
                        'prenomPas' => $passagerData['prenom'],
                        'cinPas' => $passagerData['cin'] ?? null,
                        'type_passager' => $passagerData['type_passager'] ?? 'adulte',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réservation hôtel créée avec succès',
                'reservation' => $reservation->load('passagers', 'service'),
                'limits' => $limits
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création réservation hôtel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les réservations du client connecté
     */
    public function myReservations(Request $request)
    {
        $user = $request->user();
        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            return response()->json([
                'success' => true,
                'reservations' => []
            ]);
        }

        $reservations = Reservation::with(['service', 'passagers'])
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reservations' => $reservations
        ]);
    }

    /**
     * Récupérer une réservation spécifique
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        $reservation = Reservation::with(['service', 'passagers'])
            ->where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'reservation' => $reservation
        ]);
    }

    /**
     * Annuler une réservation
     */
    public function cancel($id, Request $request)
    {
        $user = $request->user();
        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        $reservation = Reservation::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être annulée'
            ], 400);
        }

        $reservation->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée avec succès'
        ]);
    }

    /**
     * Vérifier les limites de réservation
     */
    public function checkLimits(Request $request)
    {
        $user = $request->user();
        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        $limits = $this->checkReservationLimits($client->id);

        return response()->json([
            'success' => true,
            'limits' => $limits
        ]);
    }
}
