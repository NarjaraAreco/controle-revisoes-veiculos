<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\StorePersonRequest;
use Illuminate\Http\RedirectResponse;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('people/Index', [
            'people' => Person::query()
                ->select(['id', 'name', 'cpf', 'email', 'city', 'state'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('people/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonRequest $request): RedirectResponse
    {
        Person::create($request->validated());

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
            'person' => $person,
        ]);
    }

    public function update(
        StorePersonRequest $request,
        Person $person
    ): RedirectResponse {
        $person->update($request->validated());

        return to_route('people.index')
            ->with('success', 'Pessoa atualizada com sucesso.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $person->delete();

        return to_route('people.index')
            ->with('success', 'Pessoa excluída com sucesso.');
    }
}
