<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $clientPersonId = $request->session()->get('client_person_id');

        if ($clientPersonId !== null || $user?->isClient()) {
            $personQuery = fn () => Person::query()
                ->select([
                    'id',
                    'name',
                    'cpf',
                    'email',
                    'phone',
                    'city',
                    'state',
                ])
                ->with([
                    'vehicles' => fn ($query) => $query
                        ->select([
                            'id',
                            'person_id',
                            'plate',
                            'brand',
                            'model',
                            'year',
                            'color',
                        ])
                        ->orderBy('plate')
                        ->with([
                            'revisions' => fn ($query) => $query
                                ->select([
                                    'id',
                                    'vehicle_id',
                                    'maintenance_type',
                                    'revision_date',
                                    'mileage',
                                    'next_revision_date',
                                ])
                                ->orderByDesc('revision_date'),
                        ]),
                ]);

            $person = $clientPersonId !== null
                ? $personQuery()->find($clientPersonId)
                : $user->person()
                    ->select([
                        'people.id',
                        'people.name',
                        'people.cpf',
                        'people.email',
                        'people.phone',
                        'people.city',
                        'people.state',
                    ])
                    ->with($personQuery()->getEagerLoads())
                    ->first()
                    ?? $personQuery()
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

        $revisionsByMonth = collect(DB::select(<<<'SQL'
            SELECT
                TO_CHAR(DATE_TRUNC('month', revision_date), 'YYYY-MM') AS month,
                COUNT(*) AS total
            FROM revisions
            WHERE revision_date IS NOT NULL
            GROUP BY DATE_TRUNC('month', revision_date)
            ORDER BY month
        SQL
        ))->map(fn (object $row) => [
            'month' => $row->month,
            'total' => (int) $row->total,
        ])->values();

        $totals = DB::selectOne(<<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM people) AS people,
                (SELECT COUNT(*) FROM vehicles) AS vehicles,
                (SELECT COUNT(*) FROM revisions) AS revisions
        SQL
        );

        return Inertia::render('Dashboard', [
            'totalPeople' => (int) $totals->people,
            'totalVehicles' => (int) $totals->vehicles,
            'totalRevisions' => (int) $totals->revisions,
            'vehiclesByBrand' => $vehiclesByBrand,
            'peopleWithVehicles' => $peopleWithVehicles,
            'revisionsByMonth' => $revisionsByMonth,
        ]);
    }
}
