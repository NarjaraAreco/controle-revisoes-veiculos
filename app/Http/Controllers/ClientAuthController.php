<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'birth_date' => ['required', 'date'],
        ]);

        $email = strtolower(trim($data['email']));

        $person = Person::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->whereDate('birth_date', $data['birth_date'])
            ->first();

        if ($person === null) {
            return back()->withErrors([
                'client' => 'E-mail ou data de nascimento não conferem.',
            ]);
        }

        Auth::logout();
        $request->session()->regenerate();
        $request->session()->put('client_person_id', $person->id);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
