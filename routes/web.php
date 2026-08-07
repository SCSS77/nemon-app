<?php

use App\Http\Controllers\EnergyCalculationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [EnergyCalculationController::class, 'index']);

