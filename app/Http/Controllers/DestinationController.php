<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource for client side (paginated)
     */
    public function indexCl()
    {
        $dest = Destination::paginate(6);

        return response()->json([
            'data' => $dest->items(),
            'destinations' => $dest->items(),
            'current_page' => $dest->currentPage(),
            'last_page' => $dest->lastPage(),
            'total' => $dest->total(),
        ]);
    }

    /**
     * Display a listing of the resource for admin (all destinations)
     */
    public function index()
    {
        $destinations = Destination::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $destinations
        ]);
    }

    /**
     * Get services for a specific destination (client side)
     */
    public function getServicesCl($id)
    {
        try {
            $destination = Destination::find($id);

            if (!$destination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination not found'
                ], 404);
            }

            $hotels = $destination->hotels()->with('service')->get();
            $voyages = $destination->voyages()->with('service')->get();

            return response()->json([
                'success' => true,
                'destination' => $destination,
                'offres' => [
                    'hotels' => $hotels,
                    'voyages' => $voyages
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============== ADMIN CRUD METHODS ==============

    /**
     * Store a newly created destination (Admin)
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'pays' => 'required|string|max:60',
                'ville' => 'required|string|max:255',
                'continente' => 'required|string|max:60',
                'en_vedette' => 'nullable|boolean',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
            ]);

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('images', 'public');
            }

            $destination = Destination::create([
                'pays' => $validated['pays'],
                'ville' => $validated['ville'],
                'continente' => $validated['continente'],
                'en_vedette' => $request->en_vedette ?? 0,
                'description' => $request->description ?? null,
                'image' => $imagePath,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Destination créée avec succès',
                'data' => $destination
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified destination (Admin)
     */
    public function show($id)
    {
        try {
            $destination = Destination::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $destination
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Destination non trouvée'
            ], 404);
        }
    }

    /**
     * Update the specified destination (Admin)
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $destination = Destination::findOrFail($id);

            $validated = $request->validate([
                'pays' => 'required|string|max:60',
                'ville' => 'required|string|max:255',
                'continente' => 'required|string|max:60',
                'en_vedette' => 'nullable|boolean',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($destination->image && Storage::disk('public')->exists($destination->image)) {
                    Storage::disk('public')->delete($destination->image);
                }

                $image = $request->file('image');
                $imagePath = $image->store('images', 'public');
                $destination->image = $imagePath;
            }

            $destination->pays = $validated['pays'];
            $destination->ville = $validated['ville'];
            $destination->continente = $validated['continente'];
            $destination->en_vedette = $request->en_vedette ?? 0;
            $destination->description = $request->description ?? null;
            $destination->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Destination modifiée avec succès',
                'data' => $destination
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified destination (Admin)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $destination = Destination::findOrFail($id);

            // Delete image if exists
            if ($destination->image && Storage::disk('public')->exists($destination->image)) {
                Storage::disk('public')->delete($destination->image);
            }

            $destination->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Destination supprimée avec succès'
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
     * Toggle featured status (Admin)
     */
    public function toggleFeatured($id)
    {
        try {
            $destination = Destination::findOrFail($id);
            $destination->en_vedette = !$destination->en_vedette;
            $destination->save();

            return response()->json([
                'success' => true,
                'message' => $destination->en_vedette ? 'Destination mise en vedette' : 'Destination retirée des vedettes',
                'data' => $destination
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured destinations (Client)
     */
    public function getFeatured()
    {
        try {
            $destinations = Destination::where('en_vedette', 1)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $destinations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des destinations en vedette'
            ], 500);
        }
    }

    /**
     * Search destinations (Client)
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');

            $destinations = Destination::where('pays', 'like', "%{$query}%")
                ->orWhere('ville', 'like', "%{$query}%")
                ->orWhere('continente', 'like', "%{$query}%")
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $destinations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }
}
