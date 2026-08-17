<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientProfileController;

Route::redirect('/', '/login')->name('home');

Route::post('client/login', [ClientAuthController::class, 'login'])
    ->name('client.login');

Route::post('client/logout', [ClientAuthController::class, 'logout'])
    ->name('client.logout');

Route::get('client/profile', ClientProfileController::class)
    ->middleware('client')
    ->name('client.profile');

Route::get('api/colors', function () {
    return response()->json([
        [
            'id' => 'branco',
            'name' => 'Branco',
        ],
        [
            'id' => 'preto',
            'name' => 'Preto',
        ],
        [
            'id' => 'prata',
            'name' => 'Prata',
        ],
        [
            'id' => 'cinza',
            'name' => 'Cinza',
        ],
        [
            'id' => 'vermelho',
            'name' => 'Vermelho',
        ],
        [
            'id' => 'azul',
            'name' => 'Azul',
        ],
    ]);
})->middleware(['auth', 'admin']);


Route::get('dashboard', DashboardController::class)
    ->middleware('dashboard.access')
    ->name('dashboard');

Route::get('reports', [ReportController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('reports.index');

// -------------------------------------- PESSOA----------------

Route::get('people', [PersonController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('people.index');

require __DIR__.'/settings.php';

Route::get('people/create', [PersonController::class, 'create'])
    ->middleware(['auth', 'admin'])
    ->name('people.create');

Route::post('people', [PersonController::class, 'store'])
    ->middleware(['auth', 'admin'])
    ->name('people.store');

Route::get('people/{person}/edit', [PersonController::class, 'edit'])
    ->middleware(['auth', 'admin'])
    ->name('people.edit');

Route::put('people/{person}', [PersonController::class, 'update'])
    ->middleware(['auth', 'admin'])
    ->name('people.update');

Route::delete('people/{person}', [PersonController::class, 'destroy'])
    ->middleware(['auth', 'admin'])
    ->name('people.destroy');

// ---------------------------------------------------------- VEICULOS

Route::get('vehicles', [VehicleController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('vehicles.index');

Route::get('vehicles/create', [VehicleController::class, 'create'])
    ->middleware(['auth', 'admin'])
    ->name('vehicles.create');

Route::post('vehicles', [VehicleController::class, 'store'])
    ->middleware(['auth', 'admin'])
    ->name('vehicles.store');

Route::get('vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])
    ->middleware(['auth', 'admin'])
    ->name('vehicles.edit');

Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])
    ->middleware(['auth', 'admin'])
    ->name('vehicles.update');

Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])
    ->middleware(['auth', 'admin'])
    ->name('vehicles.destroy');

Route::get('api/brands', function () {
    $brands = Cache::remember(
        'vehicle-brands',
        now()->addDay(),
        function () {
            $response = Http::timeout(10)
                ->get('https://fipe.parallelum.com.br/api/v2/cars/brands');

            $response->throw();

            return collect($response->json())
                ->map(function (array $brand) {
                    return [
                        'id' => (string) ($brand['code'] ?? ''),
                        'name' => (string) ($brand['name'] ?? ''),
                    ];
                })
                ->filter(function (array $brand) {
                    return $brand['id'] !== ''
                        && $brand['name'] !== '';
                })
                ->values()
                ->all();
        }
    );

    return response()->json($brands);
})->middleware(['auth', 'admin']);

Route::get('api/brands/{brand}/models', function (string $brand) {
    $models = Cache::remember(
        "vehicle-brand-{$brand}-models",
        now()->addDays(7),
        function () use ($brand) {
            $response = Http::timeout(10)
                ->get(
                    "https://fipe.parallelum.com.br/api/v2/cars/brands/{$brand}/models"
                );

            $response->throw();

            return collect($response->json())
                ->map(function (array $model) {
                    return [
                        'id' => (string) ($model['code'] ?? ''),
                        'name' => (string) ($model['name'] ?? ''),
                    ];
                })
                ->filter(function (array $model) {
                    return $model['id'] !== ''
                        && $model['name'] !== '';
                })
                ->values()
                ->all();
        }
    );

    return response()->json($models);
})->whereNumber('brand')->middleware(['auth', 'admin']);

//-----------------------------------------------------------------------------------

Route::get('revisions', [RevisionController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('revisions.index');

Route::get('revisions/create', [RevisionController::class, 'create'])
    ->middleware(['auth', 'admin'])
    ->name('revisions.create');

Route::post('revisions', [RevisionController::class, 'store'])
    ->middleware(['auth', 'admin'])
    ->name('revisions.store');

Route::get('revisions/{revision}/edit', [RevisionController::class, 'edit'])
    ->middleware(['auth', 'admin'])
    ->name('revisions.edit');

Route::put('revisions/{revision}', [RevisionController::class, 'update'])
    ->middleware(['auth', 'admin'])
    ->name('revisions.update');

Route::delete('revisions/{revision}', [RevisionController::class, 'destroy'])
    ->middleware(['auth', 'admin'])
    ->name('revisions.destroy');
