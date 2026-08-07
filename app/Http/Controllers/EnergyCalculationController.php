<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateEnergyRequest;
use App\Actions\CalculatePriceIndexedAction;
use App\Models\Consumption;
use App\Models\Price;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Exception;

class EnergyCalculationController extends Controller
{
    /**
     * Renders the main dashboard interface sending all table records.
     */
    public function index()
    {
        return Inertia::render('Energy/Dashboard', [
            'consumptions' => Consumption::orderBy('date', 'desc')->get(),
            'prices' => Price::orderBy('date', 'desc')->get(),
        ]);
    }

    /**
     * Handles the HTTP request to calculate the indexed price.
     */
    public function calculate(CalculateEnergyRequest $request, CalculatePriceIndexedAction $action): JsonResponse
    {
        try {
            // The Request has already validated the data. We proceed directly to the action.
            $indexedPrice = $action->execute(
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('formula')
            );

            return response()->json([
                'price_indexed' => round($indexedPrice, 4)
            ], 200);

        } catch (Exception $e) {
            $code = $e->getCode();
            $statusCode = in_array($code, [400, 404, 500]) ? $code : 500;

            return response()->json([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }
}
