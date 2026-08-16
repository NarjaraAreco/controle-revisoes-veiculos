<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // $averageRevisionIntervals = DB::query()
        //     ->fromSub(
        //         Revision::query()
        //             ->join('vehicles', 'vehicles.id', '=', 'revisions.vehicle_id')
        //             ->join('people', 'people.id', '=', 'vehicles.person_id')
        //             ->selectRaw(
        //                 'people.id as person_id,
        //         people.name,
        //         revisions.revision_date,
        //         LAG(revisions.revision_date) OVER (
        //             PARTITION BY people.id
        //             ORDER BY revisions.revision_date
        //         ) as previous_revision_date'
        //             )
        //             ->when($startDate, function ($query) use ($startDate) {
        //                 $query->whereDate('revisions.revision_date', '>=', $startDate);
        //             })
        //             ->when($endDate, function ($query) use ($endDate) {
        //                 $query->whereDate('revisions.revision_date', '<=', $endDate);
        //             })
        //             ->toBase(),
        //         'revision_history'
        //     )
        //     ->select('person_id', 'name')
        //     ->selectRaw(
        //         'AVG(revision_date - previous_revision_date) as average_days'
        //     )
        //     ->whereNotNull('previous_revision_date')
        //     ->groupBy('person_id', 'name')
        //     ->orderByDesc('average_days')
        //     ->get();

        return Inertia::render('reports/Index', [
            'totalPeople' => Person::count(),
            'totalVehicles' => Vehicle::count(),
            'totalRevisions' => Vehicle::query()
                ->withCount('revisions')
                ->get()
                ->sum('revisions_count'),
            'peopleByGender' => $peopleByGender,
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
            //'averageRevisionIntervals' => $averageRevisionIntervals,
        ]);
    }
}
