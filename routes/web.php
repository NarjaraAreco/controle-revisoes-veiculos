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
        [
            'id' => 'verde',
            'name' => 'Verde',
        ],
        [
            'id' => 'amarelo',
            'name' => 'Amarelo',
        ],
        [
            'id' => 'laranja',
            'name' => 'Laranja',
        ],
        [
            'id' => 'marrom',
            'name' => 'Marrom',
        ],
        [
            'id' => 'bege',
            'name' => 'Bege',
        ],
        [
            'id' => 'dourado',
            'name' => 'Dourado',
        ],
        [
            'id' => 'roxo',
            'name' => 'Roxo',
        ],
        [
            'id' => 'rosa',
            'name' => 'Rosa',
        ],
        [
            'id' => 'vinho',
            'name' => 'Vinho',
        ],
        [
            'id' => 'grafite',
            'name' => 'Grafite',
        ],
        [
            'id' => 'azul-marinho',
            'name' => 'Azul-marinho',
        ],
        [
            'id' => 'azul-claro',
            'name' => 'Azul-claro',
        ],
        [
            'id' => 'azul-escuro',
            'name' => 'Azul-escuro',
        ],
        [
            'id' => 'azul-petroleo',
            'name' => 'Azul-petróleo',
        ],
        [
            'id' => 'azul-turquesa',
            'name' => 'Azul-turquesa',
        ],
        [
            'id' => 'verde-claro',
            'name' => 'Verde-claro',
        ],
        [
            'id' => 'verde-escuro',
            'name' => 'Verde-escuro',
        ],
        [
            'id' => 'verde-oliva',
            'name' => 'Verde-oliva',
        ],
        [
            'id' => 'verde-musgo',
            'name' => 'Verde-musgo',
        ],
        [
            'id' => 'verde-agua',
            'name' => 'Verde-água',
        ],
        [
            'id' => 'verde-lima',
            'name' => 'Verde-limão',
        ],
        [
            'id' => 'vermelho-claro',
            'name' => 'Vermelho-claro',
        ],
        [
            'id' => 'vermelho-escuro',
            'name' => 'Vermelho-escuro',
        ],
        [
            'id' => 'bordo',
            'name' => 'Bordô',
        ],
        [
            'id' => 'cobre',
            'name' => 'Cobre',
        ],
        [
            'id' => 'bronze',
            'name' => 'Bronze',
        ],
        [
            'id' => 'champagne',
            'name' => 'Champagne',
        ],
        [
            'id' => 'perolizado',
            'name' => 'Perolizado',
        ],
        [
            'id' => 'branco-perolizado',
            'name' => 'Branco perolizado',
        ],
        [
            'id' => 'preto-metalico',
            'name' => 'Preto metálico',
        ],
        [
            'id' => 'prata-metalica',
            'name' => 'Prata metálica',
        ],
        [
            'id' => 'cinza-claro',
            'name' => 'Cinza-claro',
        ],
        [
            'id' => 'cinza-escuro',
            'name' => 'Cinza-escuro',
        ],
        [
            'id' => 'chumbo',
            'name' => 'Chumbo',
        ],
        [
            'id' => 'titanio',
            'name' => 'Titânio',
        ],
        [
            'id' => 'creme',
            'name' => 'Creme',
        ],
        [
            'id' => 'caramelo',
            'name' => 'Caramelo',
        ],
        [
            'id' => 'terracota',
            'name' => 'Terracota',
        ],
        [
            'id' => 'ocre',
            'name' => 'Ocre',
        ],
        [
            'id' => 'lilas',
            'name' => 'Lilás',
        ],
        [
            'id' => 'lavanda',
            'name' => 'Lavanda',
        ],
        [
            'id' => 'magenta',
            'name' => 'Magenta',
        ],
        [
            'id' => 'rosa-claro',
            'name' => 'Rosa-claro',
        ],
        [
            'id' => 'rosa-escuro',
            'name' => 'Rosa-escuro',
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
