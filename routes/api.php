<?php

use App\Http\Controllers\EnergyCalculationController;
use Illuminate\Support\Facades\Route;

Route::post('/calculate', [EnergyCalculationController::class, 'calculate']);