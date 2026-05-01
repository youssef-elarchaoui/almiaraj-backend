<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Envoyer un message (contact formulaire)
     */

    public function adminIndex()
    {
        try {
            $messages = Message::with('client')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des messages'
            ], 500);
        }
    }

    public function adminShow($id)
    {
        try {
            $message = Message::with('client')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        }
    }

    public function adminDestroy($id)
    {
        try {
            $message = Message::findOrFail($id);
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'statusM' => 'required|in:non_lu,lu,repondu'
            ]);

            $message = Message::findOrFail($id);
            $message->statusM = $validated['statusM'];
            $message->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut du message mis à jour',
                'data' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    public function adminReply(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reply' => 'required|string'
            ]);

            $message = Message::findOrFail($id);
            $message->reply = $validated['reply'];
            $message->statusM = 'repondu';
            $message->replied_at = now();
            $message->save();

            return response()->json([
                'success' => true,
                'message' => 'Réponse envoyée avec succès',
                'data' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la réponse'
            ], 500);
        }
    }
    public function store(Request $request)
    {
        Log::info('Message reçu', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'email' => 'required|email|max:150',
            'telephone' => 'nullable|string|max:20',
            'sujet' => 'nullable|string|max:200',
            'message' => 'required|string',
            'type' => 'nullable|string|in:contact,hajj_omra,support',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();
        $clientId = null;

        if ($user) {
            $client = Client::where('id', $user->id)->first();
            if ($client) {
                $clientId = $client->id;
            }
        }

        try {
            $prenom = $validated['prenom'] ?? '';
            $nomComplet = $prenom
                ? $prenom . ' ' . $validated['nom']
                : $validated['nom'];

            $message = Message::create([
                'nomM' => $nomComplet,
                'numTelM' => $validated['telephone'] ?? null,
                'emailM' => $validated['email'],
                'contenu' => $validated['message'],
                'dateM' => now(),
                'statusM' => Message::STATUS_PENDING,
                'client_id' => $clientId,
                'type' => $validated['type'] ?? 'contact',
            ]);

            Log::info('Message enregistré', ['message_id' => $message->id]);

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur enregistrement message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Récupérer tous les messages (Admin)
     */
    public function index()
    {
        $messages = Message::with('client')->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }


    public function showForClient($id)
    {
        $user = Auth::user();
        $client = Client::where('email', $user->email)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        $message = Message::where('id', $id)
            ->where('client_id', $client->id)
            ->with('client')
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Récupérer un message spécifique
     */
    public function show($id)
    {
        $message = Message::with('client')->findOrFail($id);

        // Marquer comme lu si nécessaire
        if ($message->statusM === Message::STATUS_PENDING) {
            $message->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Mettre à jour le statut d'un message
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:en_attente,lu,repondu,archive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::findOrFail($id);
        $message->update(['statusM' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour'
        ]);
    }

    /**
     * Supprimer un message (Admin)
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message supprimé'
        ]);
    }

    /**
     * Récupérer les messages du client connecté
     */
    public function myMessages(Request $request)
    {
        $user = $request->user();
        $client = Client::where('id', $user->id)->first();

        if (!$client) {
            return response()->json([
                'success' => true,
                'messages' => []
            ]);
        }

        $messages = Message::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
