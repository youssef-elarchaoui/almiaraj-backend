<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{

    public function index()
    {
        $hotels = Hotel::with(['service', 'destination'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hotels
        ]);
    }

    public function indexCl()
    {
        $hotels = Hotel::with(['service', 'destination'])->paginate(6);

        $data = $hotels->getCollection()->map(function ($h) {
            // Convert JSON string to comma-separated string for display
            $amenities = $h->amenities;
            if (is_string($amenities) && str_starts_with($amenities, '[')) {
                // It's JSON, decode it
                $amenitiesArray = json_decode($amenities, true);
                $amenities = is_array($amenitiesArray) ? implode(',', $amenitiesArray) : '';
            }
            
            return [
                'id' => $h->id,
                'name' => $h->service->nomServ,
                'location' => $h->villeHotel . ', ' . ($h->destination->pays ?? ''),
                'image' => $h->service->image,
                'prix' => $h->service->prix,
                'oldPrix' => $h->service->oldPrix,
                'rating' => $h->service->rating,
                'enVedette' => $h->service->enVedette,
                'amenities' => explode(',', $amenities ?? ''),
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => $hotels->currentPage(),
            'last_page' => $hotels->lastPage(),
            'total' => $hotels->total(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'nomServ' => 'required|string|max:255',
                'description' => 'nullable|string',
                'prix' => 'required|numeric|min:0',
                'rating' => 'nullable|numeric|min:0|max:5',
                'destination_id' => 'required|exists:destinations,id',
                'amenities' => 'nullable|json',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('hotels', 'public');
            }

            // Create service
            $service = Service::create([
                'nomServ' => $request->nomServ,
                'description' => $request->description,
                'prix' => $request->prix,
                'type' => 'hotel',
                'image' => $imagePath,
                'rating' => $request->rating ?? 0,
            ]);

            // Convert amenities from JSON to comma-separated string
            $amenitiesString = $request->amenities;
            if (is_string($amenitiesString) && str_starts_with($amenitiesString, '[')) {
                // It's JSON, decode and convert to comma-separated
                $amenitiesArray = json_decode($amenitiesString, true);
                $amenitiesString = is_array($amenitiesArray) ? implode(',', $amenitiesArray) : '';
            }

            // Create hotel
            $hotel = Hotel::create([
                'id' => $service->id,
                'destination_id' => $request->destination_id,
                'amenities' => $amenitiesString,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hôtel créé avec succès',
                'data' => $service->load('hotel.destination')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'hôtel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showCl($id)
    {
        try {
            $hotel = Hotel::with(['service', 'destination'])->findOrFail($id);

            // Convert amenities from JSON or comma-separated to array for frontend
            $amenities = $hotel->amenities;
            if (is_string($amenities)) {
                if (str_starts_with($amenities, '[')) {
                    // It's JSON
                    $amenities = json_decode($amenities, true);
                } else {
                    // It's comma-separated
                    $amenities = explode(',', $amenities);
                }
            }

            $chambreTypes = [
                ['value' => 'single', 'label' => 'Single (1 personne)', 'prix' => $hotel->service->prix ?? 800, 'max_personnes' => 1],
                ['value' => 'double', 'label' => 'Double (2 personnes)', 'prix' => ($hotel->service->prix ?? 800) * 2, 'max_personnes' => 2],
                ['value' => 'suite', 'label' => 'Suite (4 personnes)', 'prix' => ($hotel->service->prix ?? 800) * 4, 'max_personnes' => 4],
                ['value' => 'family', 'label' => 'Familiale (6 personnes)', 'prix' => ($hotel->service->prix ?? 800) * 6, 'max_personnes' => 6],
            ];

            $responseData = [
                'id' => $hotel->id,
                'villeHotel' => $hotel->villeHotel,
                'amenities' => $amenities,
                'destination_id' => $hotel->destination_id,
                'chambre_types' => $chambreTypes,
                'service' => $hotel->service ? [
                    'id' => $hotel->service->id,
                    'nomServ' => $hotel->service->nomServ,
                    'description' => $hotel->service->description,
                    'prix' => $hotel->service->prix,
                    'oldPrix' => $hotel->service->oldPrix,
                    'image' => $hotel->service->image,
                    'type' => $hotel->service->type,
                    'rating' => $hotel->service->rating,
                    'enVedette' => $hotel->service->enVedette,
                ] : null,
                'destination' => $hotel->destination ? [
                    'id' => $hotel->destination->id,
                    'nom' => $hotel->destination->nom,
                    'pays' => $hotel->destination->pays,
                    'continente' => $hotel->destination->continente,
                    'description' => $hotel->destination->description,
                    'image' => $hotel->destination->image,
                ] : null,
            ];

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $hotel = Hotel::with(['service', 'destination'])->find($id);

            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hôtel non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $hotel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $hotel = Hotel::findOrFail($id);
            $service = Service::findOrFail($id);

            $validated = $request->validate([
                'nomServ' => 'required|string|max:255',
                'description' => 'nullable|string',
                'prix' => 'required|numeric|min:0',
                'rating' => 'nullable|numeric|min:0|max:5',
                'destination_id' => 'required|exists:destinations,id',
                'amenities' => 'nullable|json',
            ]);

            $service->update([
                'nomServ' => $request->nomServ,
                'description' => $request->description,
                'prix' => $request->prix,
                'rating' => $request->rating ?? 0,
            ]);

            if ($request->hasFile('image')) {
                if ($service->image && Storage::disk('public')->exists($service->image)) {
                    Storage::disk('public')->delete($service->image);
                }

                $image = $request->file('image');
                $service->image = $image->store('hotels', 'public');
                $service->save();
            }

            // Convert amenities from JSON to comma-separated string
            $amenitiesString = $request->amenities;
            if (is_string($amenitiesString) && str_starts_with($amenitiesString, '[')) {
                $amenitiesArray = json_decode($amenitiesString, true);
                $amenitiesString = is_array($amenitiesArray) ? implode(',', $amenitiesArray) : '';
            }

            $hotel->update([
                'destination_id' => $request->destination_id,
                'amenities' => $amenitiesString,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hôtel modifié avec succès',
                'data' => $service->load('hotel.destination')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $hotel = Hotel::findOrFail($id);
            $service = Service::findOrFail($id);

            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }

            $hotel->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hôtel supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}