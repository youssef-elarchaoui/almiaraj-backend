<?php
// app/Http/Controllers/HajjOmraController.php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\HajjOmra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HajjOmraController extends Controller
{

    public function indexCl()
    {
        $items = HajjOmra::with('service')->paginate(6);

        $data = $items->getCollection()->map(function ($h) {
            return [
                'id' => $h->id,
                'title' => $h->service->nomServ,
                'depart' => $h->dateDepartHO,
                'retour' => $h->dateRetourHO,
                'duration' => $h->duree . ' jours',
                'price' => $h->service->prix,
                'oldPrice' => $h->service->oldPrix,
                'groupSize' => $h->typeChambre,
                'hotel' => $h->hotel,
                'transport' => $h->transport,
                'meals' => $h->meals,
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'total' => $items->total(),
        ]);
    }
    public function index()
    {
        $hajjOmras = HajjOmra::with('service')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hajjOmras
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Calculate duration
            $dateDepart = Carbon::parse($request->dateDepartHO);
            $dateRetour = Carbon::parse($request->dateRetourHO);
            $dureeJours = $dateDepart->diffInDays($dateRetour);
            $duree = $dureeJours . ' jours / ' . ($dureeJours - 1) . ' nuits';

            // Validation - NO image validation here (same as voyage)
            $validated = $request->validate([
                'nomServ' => 'required|string|max:255',
                'description' => 'nullable|string',
                'prix' => 'required|numeric|min:0',
                'type' => 'required|in:hajj,omra',
                'formule' => 'required|string|max:100',
                'dateDepartHO' => 'required|date',
                'dateRetourHO' => 'required|date|after_or_equal:dateDepartHO',
                'typeChambre' => 'required|string|max:50',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('hajj_omras', 'public');
            }

            $service = Service::create([
                'nomServ' => $request->nomServ,
                'description' => $request->description,
                'prix' => $request->prix,
                'type' => 'hajjOmra',
                'image' => $imagePath,
            ]);

            $hajjOmra = HajjOmra::create([
                'id' => $service->id,
                'type' => $request->type,
                'formule' => $request->formule,
                'dateDepartHO' => $request->dateDepartHO,
                'dateRetourHO' => $request->dateRetourHO,
                'duree' => $duree,
                'typeChambre' => $request->typeChambre,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service Hajj/Omra créé avec succès',
                'data' => $service->load('hajjOmra')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $hajjOmra = HajjOmra::with('service')->find($id);

            if (!$hajjOmra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $hajjOmra
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

            $hajjOmra = HajjOmra::findOrFail($id);
            $service = Service::findOrFail($id);

            // Calculate duration
            $dateDepart = Carbon::parse($request->dateDepartHO);
            $dateRetour = Carbon::parse($request->dateRetourHO);
            $dureeJours = $dateDepart->diffInDays($dateRetour);
            $duree = $dureeJours . ' jours / ' . ($dureeJours - 1) . ' nuits';

            // Validation - NO image validation here
            $validated = $request->validate([
                'nomServ' => 'required|string|max:255',
                'description' => 'nullable|string',
                'prix' => 'required|numeric|min:0',
                'type' => 'required|in:hajj,omra',
                'formule' => 'required|string|max:100',
                'dateDepartHO' => 'required|date',
                'dateRetourHO' => 'required|date|after_or_equal:dateDepartHO',
                'typeChambre' => 'required|string|max:50',
            ]);

            $service->update([
                'nomServ' => $request->nomServ,
                'description' => $request->description,
                'prix' => $request->prix,
            ]);

            if ($request->hasFile('image')) {
                if ($service->image && Storage::disk('public')->exists($service->image)) {
                    Storage::disk('public')->delete($service->image);
                }
                $image = $request->file('image');
                $service->image = $image->store('hajj_omras', 'public');
                $service->save();
            }

            $hajjOmra->update([
                'type' => $request->type,
                'formule' => $request->formule,
                'dateDepartHO' => $request->dateDepartHO,
                'dateRetourHO' => $request->dateRetourHO,
                'duree' => $duree,
                'typeChambre' => $request->typeChambre,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service Hajj/Omra modifié avec succès'
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

            $hajjOmra = HajjOmra::findOrFail($id);
            $service = Service::findOrFail($id);

            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }

            $hajjOmra->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service supprimé avec succès'
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

    public function showCl($id)
    {
        $hajjOmra = HajjOmra::with('service')->findOrFail($id);
        return response()->json($hajjOmra);
    }
}
