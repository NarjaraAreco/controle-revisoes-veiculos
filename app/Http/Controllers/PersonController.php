<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\StorePersonRequest;
use App\Concerns\DataCache;
use Illuminate\Http\RedirectResponse;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('people/Index', [
            'people' => DataCache::remember('people.index', fn () => Person::query()
                ->select(['id', 'name', 'cpf', 'email', 'city', 'state'])
                ->orderBy('name')
                ->get()
                ->map(fn (Person $person): array => $person->toArray())
                ->all()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('people/Create', [
            'existingCpfs' => DataCache::remember('people.cpf-list', fn () => Person::query()
                ->pluck('cpf')
                ->map(fn ($cpf) => (string) $cpf)
                ->all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonRequest $request): RedirectResponse
    {
        Person::create($request->validated());
        DataCache::invalidate();

        return to_route('people.index')
            ->with('success', 'Pessoa cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Person $person): Response
    {
        return Inertia::render('people/Edit', [
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
            'existingCpfs' => DataCache::remember("people.cpf-list.except.{$person->id}", fn () => Person::query()
                ->whereKeyNot($person->id)
                ->pluck('cpf')
                ->map(fn ($cpf) => (string) $cpf)
                ->all()),
        ]);
    }

    public function update(
        StorePersonRequest $request,
        Person $person
    ): RedirectResponse {
        $person->update($request->validated());
        DataCache::invalidate();

        return to_route('people.index')
            ->with('success', 'Pessoa atualizada com sucesso.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $person->delete();
        DataCache::invalidate();

        return to_route('people.index')
            ->with('success', 'Pessoa excluída com sucesso.');
    }
}
