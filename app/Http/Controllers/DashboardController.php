<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Revision;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $clientPersonId = $request->session()->get('client_person_id');

        if ($clientPersonId !== null || $user?->isClient()) {
            $person = $clientPersonId !== null
                ? Person::query()->with('vehicles.revisions')->find($clientPersonId)
                : $user->person()
                    ->with('vehicles.revisions')
                    ->first()
                    ?? Person::query()
                        ->with('vehicles.revisions')
                        ->where('email', $user->email)
                        ->first();

            abort_if($person === null, 403, 'Cliente sem pessoa vinculada.');

            return Inertia::render('ClientDashboard', [
                'person' => $person,
            ]);
        }

        $vehiclesByBrand = Vehicle::query()
            ->selectRaw('brand, COUNT(*) as total')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'brand' => $item->brand,
                'total' => (int) $item->total,
            ])
            ->values();

        $peopleWithVehicles = Person::query()
            ->withCount('vehicles')
            ->orderByDesc('vehicles_count')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($person) => [
                'id' => $person->id,
                'name' => $person->name,
                'total' => (int) $person->vehicles_count,
            ])
            ->values();

        $revisionsByMonth = Revision::query()
            ->orderBy('revision_date')
            ->get(['revision_date'])
            ->filter(fn ($revision) => $revision->revision_date !== null)
            ->groupBy(fn ($revision) => $revision->revision_date->format('Y-m'))
            ->map(fn ($revisions, $month) => [
                'month' => $month,
                'total' => $revisions->count(),
            ])
            ->values();

        return Inertia::render('Dashboard', [
            'totalPeople' => Person::count(),
            'totalVehicles' => Vehicle::count(),
            'totalRevisions' => Revision::count(),
            'vehiclesByBrand' => $vehiclesByBrand,
            'peopleWithVehicles' => $peopleWithVehicles,
            'revisionsByMonth' => $revisionsByMonth,
        ]);
    }
}
