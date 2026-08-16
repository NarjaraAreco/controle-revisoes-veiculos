<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Revision;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'totalPeople' => Person::count(),
            'totalVehicles' => Vehicle::count(),
            'totalRevisions' => Revision::count(),
        ]);
    }
}
