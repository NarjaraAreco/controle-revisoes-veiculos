<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

Route::inertia('/', 'Welcome')->name('home');

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
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('people', [PersonController::class, 'index'])
    ->middleware(['auth'])
    ->name('people.index');

require __DIR__.'/settings.php';

Route::get('people/create', [PersonController::class, 'create'])
    ->middleware(['auth'])
    ->name('people.create');

Route::post('people', [PersonController::class, 'store'])
    ->middleware(['auth'])
    ->name('people.store');

Route::get('vehicles', [VehicleController::class, 'index'])
    ->middleware(['auth'])
    ->name('vehicles.index');

Route::get('vehicles/create', [VehicleController::class, 'create'])
    ->middleware(['auth'])
    ->name('vehicles.create');

Route::post('vehicles', [VehicleController::class, 'store'])
    ->middleware(['auth'])
    ->name('vehicles.store');

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
});

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
})->whereNumber('brand');
