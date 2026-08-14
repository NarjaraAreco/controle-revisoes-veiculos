<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Person;
use App\Http\Requests\StoreVehicleRequest;
use Illuminate\Http\RedirectResponse;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('vehicles/Index', [
            'vehicles' => Vehicle::with('person')
                ->orderBy('brand')
                ->orderBy('model')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('vehicles/Create', [
            'people' => Person::query()
                ->orderBy('name')
                ->get(['id', 'name']),

            'selectedPersonId' => $request->integer('person_id') ?: null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        Vehicle::create($request->validated());

        return to_route('vehicles.index')
            ->with('success', 'Veículo cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Vehicle $vehicle): Response
    {
        return Inertia::render('vehicles/Edit', [
            'vehicle' => $vehicle,
            'people' => Person::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(
        StoreVehicleRequest $request,
        Vehicle $vehicle
    ): RedirectResponse {
        $vehicle->update($request->validated());

        return to_route('vehicles.index')
            ->with('success', 'Veículo atualizado com sucesso.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return to_route('vehicles.index')
            ->with('success', 'Ve   ículo excluído com sucesso.');
    }
}
