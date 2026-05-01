<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Reservation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            $client = Client::create([
                'id' => $user->id,  
                'nomCl' => explode(' ', $user->name)[0] ?? '',
                'prenomCl' => explode(' ', $user->name)[1] ?? '',
                'email' => $user->email,
                'numTelCl' => '',
                'natCl' => 'maroc',
                'dateInscription' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'client' => $client
        ]);
    }

    /**
     * Admin: Get all clients
     */
    public function adminIndex()
{
    try {
        // Get only clients where user role is 'client'
        $clients = Client::whereHas('user', function($query) {
            $query->where('role', 'user');
        })->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du chargement des clients',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Admin: Get single client with reservations, avis, and messages
     */
    public function adminShow($id)
    {
        try {
            $client = Client::with([
                'reservations.service',
                'avis.service',
                'messages'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }
    }

    /**
     * Admin: Delete client and all their reservations
     */
    public function adminDestroy($id)
    {
        try {
            DB::beginTransaction();

            $client = Client::findOrFail($id);

            // Delete all reservations for this client
            Reservation::where('client_id', $id)->delete();

            // Delete the client
            $client->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Original code preserved - not modified
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            $client = Client::create([
                'id' => $user->id,
                'nomCl' => $request->nomCl ?? '',
                'prenomCl' => $request->prenomCl ?? '',
                'email' => $user->email,
                'numTelCl' => $request->numTelCl ?? '',
                'natCl' => $request->natCl ?? 'maroc',
                'cin' => $request->cin ?? null,
                'passport' => $request->passport ?? null,
                'dateInscription' => now(),
            ]);
        } else {
            $client->update([
                'nomCl' => $request->nomCl ?? $client->nomCl,
                'prenomCl' => $request->prenomCl ?? $client->prenomCl,
                'numTelCl' => $request->numTelCl ?? $client->numTelCl,
                'natCl' => $request->natCl ?? $client->natCl,
                'cin' => $request->cin ?? $client->cin,
                'passport' => $request->passport ?? $client->passport,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'client' => $client
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }
}
