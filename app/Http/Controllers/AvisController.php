<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvisController extends Controller
{
    // Get all avis for admin
    public function adminIndex()
    {
        try {
            $avis = Avis::with(['client', 'service'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $avis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des avis'
            ], 500);
        }
    }

    // Get single avis for admin
    public function adminShow($id)
    {
        try {
            $avis = Avis::with(['client', 'service'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $avis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Avis non trouvé'
            ], 404);
        }
    }

    // Delete avis
    public function adminDestroy($id)
    {
        try {
            $avis = Avis::findOrFail($id);
            $avis->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Avis supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    // Get avis by service (for frontend)
    public function getByService($serviceId)
    {
        try {
            $avis = Avis::with('client')
                ->where('service_id', $serviceId)
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $avis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des avis'
            ], 500);
        }
    }

    // Store new avis (for frontend - client)
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'commentaire' => 'required|string|min:3',
                'note' => 'required|integer|min:1|max:5'
            ]);

            $user = $request->user();
            $client = Client::where('id', $user->id)->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ], 404);
            }

            $avis = Avis::create([
                'service_id' => $validated['service_id'],
                'client_id' => $client->id,
                'commentaire' => $validated['commentaire'],
                'note' => $validated['note'],
                'dateAv' => now(),
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avis ajouté avec succès',
                'data' => $avis
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de l\'avis'
            ], 500);
        }
    }

    // Update avis status (approve/reject)
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $avis = Avis::findOrFail($id);
            $avis->status = $validated['status'];
            $avis->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de l\'avis mis à jour',
                'data' => $avis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }
}