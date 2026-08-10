<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRevisionRequest;
use App\Models\Revision;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RevisionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('revisions/Index', [
            'revisions' => Revision::with('vehicle.person')
                ->orderByDesc('revision_date')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('revisions/Create', [
            'vehicles' => Vehicle::with('person')
                ->orderBy('plate')
                ->get([
                    'id',
                    'person_id',
                    'plate',
                    'brand',
                    'model',
                ]),
        ]);
    }

    public function store(StoreRevisionRequest $request): RedirectResponse
    {
        Revision::create($request->validated());

        return to_route('revisions.index')
            ->with('success', 'Revisão cadastrada com sucesso.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Revision $revision): Response
    {
        return Inertia::render('revisions/Edit', [
            'revision' => $revision,
            'vehicles' => Vehicle::with('person')
                ->orderBy('plate')
                ->get([
                    'id',
                    'person_id',
                    'plate',
                    'brand',
                    'model',
                ]),
        ]);
    }

    public function update(
        StoreRevisionRequest $request,
        Revision $revision
    ): RedirectResponse {
        $revision->update($request->validated());

        return to_route('revisions.index')
            ->with('success', 'Revisão atualizada com sucesso.');
    }

    public function destroy(Revision $revision): RedirectResponse
    {
        $revision->delete();

        return to_route('revisions.index')
            ->with('success', 'Revisão excluída com sucesso.');
    }
}
