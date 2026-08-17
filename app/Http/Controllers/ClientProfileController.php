<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientProfileController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $person = Person::query()->find($request->session()->get('client_person_id'));

        abort_if($person === null, 403, 'Cliente sem pessoa vinculada.');

        return Inertia::render('ClientProfile', [
            'person' => $person->only([
                'id',
                'name',
                'cpf',
                'birth_date',
                'gender',
                'phone',
                'email',
                'cep',
                'street',
                'number',
                'complement',
                'neighborhood',
                'city',
                'state',
            ]),
        ]);
    }
}
