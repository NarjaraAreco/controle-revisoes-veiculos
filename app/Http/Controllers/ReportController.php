<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Revision;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $vehiclesByBrand = Vehicle::query()
            ->selectRaw('brand, COUNT(*) as total')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->get();

        $peopleWithVehicles = Person::query()
            ->withCount('vehicles')
            ->orderByDesc('vehicles_count')
            ->get(['id', 'name', 'gender']);

        $allPeople = Person::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'cpf',
                'birth_date',
                'gender',
                'email',
                'phone',
                'city',
                'state',
            ]);

        $peopleByGender = Person::query()
            ->select('gender')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'AVG(EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date))) as average_age'
            )
            ->whereIn('gender', ['Feminino', 'Masculino'])
            ->groupBy('gender')
            ->orderBy('gender')
            ->get();

        $vehiclesByPerson = Person::query()
            ->with([
                'vehicles' => function ($query) {
                    $query
                        ->orderBy('brand')
                        ->orderBy('model');
                },
            ])
            ->whereHas('vehicles')
            ->orderBy('name')
            ->get(['id', 'name', 'gender']);

        $peopleWithMostVehiclesByGender = Person::query()
            ->withCount('vehicles')
            ->whereIn('gender', ['Feminino', 'Masculino'])
            ->whereHas('vehicles')
            ->orderBy('gender')
            ->orderByDesc('vehicles_count')
            ->get(['id', 'name', 'gender'])
            ->groupBy('gender')
            ->map(function ($people) {
                return $people->first();
            })
            ->values();

        $brandsByGender = Vehicle::query()
            ->join('people', 'people.id', '=', 'vehicles.person_id')
            ->select('vehicles.brand', 'people.gender')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('people.gender', ['Feminino', 'Masculino'])
            ->groupBy('vehicles.brand', 'people.gender')
            ->orderBy('vehicles.brand')
            ->orderByDesc('total')
            ->get();

        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        $revisionsInPeriod = Revision::query()
            ->with('vehicle.person')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('revision_date', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('revision_date', '<=', $endDate);
            })
            ->orderByDesc('revision_date')
            ->get();

        $averageRevisionIntervals = $revisionsInPeriod
            ->filter(fn ($revision) => $revision->vehicle?->person)
            ->groupBy(fn ($revision) => $revision->vehicle->person->id)
            ->map(function ($revisions) {
                $orderedRevisions = $revisions->sortBy('revision_date')->values();
                $intervals = [];

                for ($index = 1; $index < $orderedRevisions->count(); $index++) {
                    $intervals[] = $orderedRevisions[$index - 1]
                        ->revision_date
                        ->diffInDays($orderedRevisions[$index]->revision_date);
                }

                if ($intervals === []) {
                    return null;
                }

                return [
                    'person_id' => $orderedRevisions->first()->vehicle->person->id,
                    'name' => $orderedRevisions->first()->vehicle->person->name,
                    'average_days' => round(array_sum($intervals) / count($intervals), 1),
                ];
            })
            ->filter()
            ->sortByDesc('average_days')
            ->values();

        $nextRevisions = $revisionsInPeriod
            ->filter(fn ($revision) => $revision->vehicle?->person)
            ->groupBy(fn ($revision) => $revision->vehicle->person->id)
            ->map(function ($revisions) use ($averageRevisionIntervals) {
                $lastRevision = $revisions
                    ->sortByDesc('revision_date')
                    ->first();
                $person = $lastRevision->vehicle->person;
                $average = $averageRevisionIntervals->firstWhere('person_id', $person->id);

                if (!$average) {
                    return null;
                }

                $averageDays = (int) round($average['average_days']);

                return [
                    'person_id' => $person->id,
                    'name' => $person->name,
                    'last_revision_date' => $lastRevision->revision_date->toDateString(),
                    'average_days' => $average['average_days'],
                    'next_revision_date' => $lastRevision->revision_date
                        ->copy()
                        ->addDays($averageDays)
                        ->toDateString(),
                ];
            })
            ->filter()
            ->sortBy('next_revision_date')
            ->values();

        $revisionsByBrand = Revision::query()
            ->join('vehicles', 'vehicles.id', '=', 'revisions.vehicle_id')
            ->select('vehicles.brand')
            ->selectRaw('COUNT(revisions.id) as total')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('revisions.revision_date', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('revisions.revision_date', '<=', $endDate);
            })
            ->groupBy('vehicles.brand')
            ->orderByDesc('total')
            ->get();

        $peopleByRevisionCount = Revision::query()
            ->join('vehicles', 'vehicles.id', '=', 'revisions.vehicle_id')
            ->join('people', 'people.id', '=', 'vehicles.person_id')
            ->select('people.id', 'people.name', 'people.gender')
            ->selectRaw('COUNT(revisions.id) as total')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('revisions.revision_date', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('revisions.revision_date', '<=', $endDate);
            })
            ->groupBy('people.id', 'people.name', 'people.gender')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('reports/Index', [
            'peopleByGender' => $peopleByGender,
            'allPeople' => $allPeople,
            'vehiclesByBrand' => $vehiclesByBrand,
            'peopleWithVehicles' => $peopleWithVehicles,
            'vehiclesByPerson' => $vehiclesByPerson,
            'peopleWithMostVehiclesByGender' => $peopleWithMostVehiclesByGender,
            'brandsByGender' => $brandsByGender,
            'revisionsInPeriod' => $revisionsInPeriod,
            'revisionFilters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'revisionsByBrand' => $revisionsByBrand,
            'peopleByRevisionCount' => $peopleByRevisionCount,
            'averageRevisionIntervals' => $averageRevisionIntervals,
            'nextRevisions' => $nextRevisions,
        ]);
    }
}
