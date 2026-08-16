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
        ]);
    }
}
