<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nom' => ['required'],
            'prenom' => ['required'],
            'nat' => ['required'],
            'numTel' => ['required'],
        ]);

        $user = User::create([
            'name' => $request->nom . ' ' . $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),

        ]);

        Client::create([
            'id' => $user->id,
            'nomCl' => $request->nom,
            'prenomCl' => $request->prenom,
            'natCl' => $request->nat,
            'numTelCl' => $request->numTel,
            'email' => $request->email,
            'dateInscription' => now(),
        ]);


        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
