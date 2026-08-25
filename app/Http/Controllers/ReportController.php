<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        [$revisionWhereSql, $revisionBindings] = $this->revisionFilters(
            $startDate,
            $endDate,
        );

        $vehiclesByBrand = collect($this->selectCached(<<<'SQL'
            SELECT brand, COUNT(*) AS total
            FROM vehicles
            GROUP BY brand
            ORDER BY total DESC, brand
        SQL
        ))->map(fn (object $row) => [
            'brand' => $row->brand,
            'total' => (int) $row->total,
        ])->values();

        $peopleWithVehicles = collect($this->selectCached(<<<'SQL'
            SELECT
                p.id,
                p.name,
                p.gender,
                COUNT(v.id) AS vehicles_count
            FROM people AS p
            LEFT JOIN vehicles AS v ON v.person_id = p.id
            GROUP BY p.id, p.name, p.gender
            ORDER BY vehicles_count DESC, p.name
        SQL
        ))->map(fn (object $row) => [
            'id' => (int) $row->id,
            'name' => $row->name,
            'gender' => $row->gender,
            'vehicles_count' => (int) $row->vehicles_count,
        ])->values();

        $allPeople = $this->selectCached(<<<'SQL'
            SELECT
                id,
                name,
                cpf,
                birth_date,
                gender,
                email,
                phone,
                city,
                state
            FROM people
            ORDER BY name
        SQL);

        $peopleByCity = collect($this->selectCached(<<<'SQL'
            SELECT city, COUNT(*) AS total
            FROM people
            WHERE city IS NOT NULL AND city <> ''
            GROUP BY city
            ORDER BY total DESC, city
        SQL
        ))->map(fn (object $row) => [
            'city' => $row->city,
            'total' => (int) $row->total,
        ])->values();

        $peopleByGender = collect($this->selectCached(<<<'SQL'
            SELECT
                gender,
                COUNT(*) AS total,
                ROUND(
                    AVG(EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)))::numeric,
                    1
                ) AS average_age
            FROM people
            WHERE gender IN ('Feminino', 'Masculino')
            GROUP BY gender
            ORDER BY gender
        SQL
        ))->map(fn (object $row) => [
            'gender' => $row->gender,
            'total' => (int) $row->total,
            'average_age' => $row->average_age !== null
                ? (float) $row->average_age
                : null,
        ])->values();

        $vehiclesByPersonRows = $this->selectCached(<<<'SQL'
            SELECT
                p.id,
                p.name,
                p.gender,
                v.id AS vehicle_id,
                v.plate,
                v.brand,
                v.model,
                v.year,
                v.color
            FROM people AS p
            JOIN vehicles AS v ON v.person_id = p.id
            ORDER BY p.name, v.brand, v.model
        SQL);

        $vehiclesByPerson = collect($vehiclesByPersonRows)
            ->groupBy('id')
            ->map(function ($rows) {
                $person = $rows->first();

                return [
                    'id' => (int) $person->id,
                    'name' => $person->name,
                    'gender' => $person->gender,
                    'vehicles' => $rows->map(fn (object $row) => [
                        'id' => (int) $row->vehicle_id,
                        'plate' => $row->plate,
                        'brand' => $row->brand,
                        'model' => $row->model,
                        'year' => (int) $row->year,
                        'color' => $row->color,
                    ])->values()->all(),
                ];
            })
            ->values();

        $allVehicles = collect($this->selectCached(<<<'SQL'
            SELECT
                v.id,
                v.plate,
                v.brand,
                v.model,
                v.year,
                v.color,
                p.id AS person_id,
                p.name AS person_name
            FROM vehicles AS v
            LEFT JOIN people AS p ON p.id = v.person_id
            ORDER BY v.brand, v.model
        SQL
        ))->map(fn (object $row) => [
            'id' => (int) $row->id,
            'plate' => $row->plate,
            'brand' => $row->brand,
            'model' => $row->model,
            'year' => (int) $row->year,
            'color' => $row->color,
            'person' => $row->person_id !== null
                ? [
                    'id' => (int) $row->person_id,
                    'name' => $row->person_name,
                ]
                : null,
        ])->values();

        $vehiclesByYear = collect($this->selectCached(<<<'SQL'
            SELECT year, COUNT(*) AS total
            FROM vehicles
            GROUP BY year
            ORDER BY year
        SQL
        ))->map(fn (object $row) => [
            'year' => (int) $row->year,
            'total' => (int) $row->total,
        ])->values();

        $peopleWithMostVehiclesByGender = collect($this->selectCached(<<<'SQL'
            WITH vehicle_counts AS (
                SELECT
                    p.id,
                    p.name,
                    p.gender,
                    COUNT(v.id) AS vehicles_count,
                    ROW_NUMBER() OVER (
                        PARTITION BY p.gender
                        ORDER BY COUNT(v.id) DESC, p.name
                    ) AS position
                FROM people AS p
                JOIN vehicles AS v ON v.person_id = p.id
                WHERE p.gender IN ('Feminino', 'Masculino')
                GROUP BY p.id, p.name, p.gender
            )
            SELECT id, name, gender, vehicles_count
            FROM vehicle_counts
            WHERE position = 1
            ORDER BY gender
        SQL
        ))->map(fn (object $row) => [
            'id' => (int) $row->id,
            'name' => $row->name,
            'gender' => $row->gender,
            'vehicles_count' => (int) $row->vehicles_count,
        ])->values();

        $brandsByGender = collect($this->selectCached(<<<'SQL'
            SELECT
                v.brand,
                p.gender,
                COUNT(*) AS total
            FROM vehicles AS v
            JOIN people AS p ON p.id = v.person_id
            WHERE p.gender IN ('Feminino', 'Masculino')
            GROUP BY v.brand, p.gender
            ORDER BY v.brand, total DESC
        SQL
        ))->map(fn (object $row) => [
            'brand' => $row->brand,
            'gender' => $row->gender,
            'total' => (int) $row->total,
        ])->values();

        $revisionsInPeriodRows = $this->selectCached(
            <<<SQL
                SELECT
                    r.id,
                    r.maintenance_type,
                    r.revision_date,
                    r.mileage,
                    r.cost,
                    r.next_revision_date,
                    v.plate,
                    v.brand,
                    v.model,
                    p.name AS person_name
                FROM revisions AS r
                JOIN vehicles AS v ON v.id = r.vehicle_id
                LEFT JOIN people AS p ON p.id = v.person_id
                {$revisionWhereSql}
                ORDER BY r.revision_date DESC
            SQL
            ,
            $revisionBindings,
        );

        $revisionsInPeriod = collect($revisionsInPeriodRows)->map(
            fn (object $row) => [
                'id' => (int) $row->id,
                'maintenance_type' => $row->maintenance_type,
                'revision_date' => $row->revision_date,
                'mileage' => (int) $row->mileage,
                'cost' => $row->cost !== null ? (float) $row->cost : null,
                'next_revision_date' => $row->next_revision_date,
                'vehicle' => [
                    'plate' => $row->plate,
                    'brand' => $row->brand,
                    'model' => $row->model,
                    'person' => $row->person_name !== null
                        ? ['name' => $row->person_name]
                        : null,
                ],
            ],
        )->values();

        $revisionsByMonth = collect($this->selectCached(
            <<<SQL
                SELECT
                    TO_CHAR(DATE_TRUNC('month', r.revision_date), 'YYYY-MM') AS month,
                    COUNT(*) AS total
                FROM revisions AS r
                {$revisionWhereSql}
                GROUP BY DATE_TRUNC('month', r.revision_date)
                ORDER BY month
            SQL
            ,
            $revisionBindings,
        ))->map(fn (object $row) => [
            'month' => $row->month,
            'total' => (int) $row->total,
        ])->values();

        $revisionsByBrand = collect($this->selectCached(
            <<<SQL
                SELECT v.brand, COUNT(r.id) AS total
                FROM revisions AS r
                JOIN vehicles AS v ON v.id = r.vehicle_id
                {$revisionWhereSql}
                GROUP BY v.brand
                ORDER BY total DESC, v.brand
            SQL
            ,
            $revisionBindings,
        ))->map(fn (object $row) => [
            'brand' => $row->brand,
            'total' => (int) $row->total,
        ])->values();

        $peopleByRevisionCount = collect($this->selectCached(
            <<<SQL
                SELECT
                    p.id,
                    p.name,
                    p.gender,
                    COUNT(r.id) AS total
                FROM revisions AS r
                JOIN vehicles AS v ON v.id = r.vehicle_id
                JOIN people AS p ON p.id = v.person_id
                {$revisionWhereSql}
                GROUP BY p.id, p.name, p.gender
                ORDER BY total DESC, p.name
            SQL
            ,
            $revisionBindings,
        ))->map(fn (object $row) => [
            'id' => (int) $row->id,
            'name' => $row->name,
            'gender' => $row->gender,
            'total' => (int) $row->total,
        ])->values();

        $averageRevisionIntervals = collect($this->selectCached(
            <<<SQL
                WITH ordered_revisions AS (
                    SELECT
                        p.id AS person_id,
                        p.name,
                        r.revision_date,
                        LAG(r.revision_date) OVER (
                            PARTITION BY p.id
                            ORDER BY r.revision_date
                        ) AS previous_revision_date
                    FROM revisions AS r
                    JOIN vehicles AS v ON v.id = r.vehicle_id
                    JOIN people AS p ON p.id = v.person_id
                    {$revisionWhereSql}
                )
                SELECT
                    person_id,
                    name,
                    ROUND(AVG(revision_date - previous_revision_date)::numeric, 1)
                        AS average_days
                FROM ordered_revisions
                WHERE previous_revision_date IS NOT NULL
                GROUP BY person_id, name
                ORDER BY average_days DESC
            SQL
            ,
            $revisionBindings,
        ))->map(fn (object $row) => [
            'person_id' => (int) $row->person_id,
            'name' => $row->name,
            'average_days' => (float) $row->average_days,
        ])->values();

        $nextRevisions = collect($this->selectCached(
            <<<SQL
                WITH ordered_revisions AS (
                    SELECT
                        p.id AS person_id,
                        p.name,
                        r.revision_date,
                        LAG(r.revision_date) OVER (
                            PARTITION BY p.id
                            ORDER BY r.revision_date
                        ) AS previous_revision_date,
                        ROW_NUMBER() OVER (
                            PARTITION BY p.id
                            ORDER BY r.revision_date DESC
                        ) AS revision_order
                    FROM revisions AS r
                    JOIN vehicles AS v ON v.id = r.vehicle_id
                    JOIN people AS p ON p.id = v.person_id
                    {$revisionWhereSql}
                ),
                average_intervals AS (
                    SELECT
                        person_id,
                        name,
                        AVG(revision_date - previous_revision_date) AS average_days
                    FROM ordered_revisions
                    WHERE previous_revision_date IS NOT NULL
                    GROUP BY person_id, name
                ),
                latest_revisions AS (
                    SELECT person_id, name, revision_date AS last_revision_date
                    FROM ordered_revisions
                    WHERE revision_order = 1
                )
                SELECT
                    l.person_id,
                    l.name,
                    l.last_revision_date,
                    ROUND(a.average_days::numeric, 1) AS average_days,
                    l.last_revision_date + ROUND(a.average_days)::integer
                        AS next_revision_date
                FROM latest_revisions AS l
                JOIN average_intervals AS a ON a.person_id = l.person_id
                ORDER BY next_revision_date
            SQL
            ,
            $revisionBindings,
        ))->map(fn (object $row) => [
            'person_id' => (int) $row->person_id,
            'name' => $row->name,
            'last_revision_date' => $row->last_revision_date,
            'average_days' => (float) $row->average_days,
            'next_revision_date' => $row->next_revision_date,
        ])->values();

        return Inertia::render('reports/Index', [
            'peopleByGender' => $peopleByGender,
            'allPeople' => $allPeople,
            'peopleByCity' => $peopleByCity,
            'vehiclesByBrand' => $vehiclesByBrand,
            'peopleWithVehicles' => $peopleWithVehicles,
            'vehiclesByPerson' => $vehiclesByPerson,
            'allVehicles' => $allVehicles,
            'vehiclesByYear' => $vehiclesByYear,
            'peopleWithMostVehiclesByGender' => $peopleWithMostVehiclesByGender,
            'brandsByGender' => $brandsByGender,
            'revisionsInPeriod' => $revisionsInPeriod,
            'revisionsByMonth' => $revisionsByMonth,
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

    /**
     * Cacheia somente o resultado da consulta, mantendo a montagem do payload
     * fora do cache e permitindo que cada combinação de filtro tenha sua chave.
     *
     * @param  array<int, mixed>  $bindings
     * @return array<int, object>
     */
    private function selectCached(string $sql, array $bindings = []): array
    {
        $key = 'report-query:'.md5($sql.'|'.serialize($bindings));

        /** @var array<int, object> $rows */
        $rows = Cache::remember(
            $key,
            now()->addSeconds(30),
            fn (): array => DB::select($sql, $bindings),
        );

        return $rows;
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function revisionFilters(?string $startDate, ?string $endDate): array
    {
        $conditions = [];
        $bindings = [];

        if ($startDate !== null) {
            $conditions[] = 'r.revision_date >= ?';
            $bindings[] = $startDate;
        }

        if ($endDate !== null) {
            $conditions[] = 'r.revision_date <= ?';
            $bindings[] = $endDate;
        }

        return [
            $conditions === []
                ? ''
                : 'WHERE '.implode(' AND ', $conditions),
            $bindings,
        ];
    }
}
