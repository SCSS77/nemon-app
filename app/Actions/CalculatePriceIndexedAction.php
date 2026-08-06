<?php

namespace App\Actions;

use App\Models\Consumption;
use App\Models\Price;
use Exception;

class CalculatePriceIndexedAction
{
    /**
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $formula
     * @return float
     * @throws Exception
     */
    public function execute(string $startDate, string $endDate, string $formula): float
    {
        // 1. DATA RETRIEVAL
        $consumptions = Consumption::whereBetween('date', [$startDate, $endDate])->orderBy('date')->get();
        $prices = Price::whereBetween('date', [$startDate, $endDate])->orderBy('date')->get();

        $expectedDays = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

        if ($consumptions->count() < $expectedDays || $prices->count() < $expectedDays) {
            throw new Exception('Missing data', 404);
        }

        $totalImporte = 0;
        $totalConsumo = 0;
        $pricesByDate = $prices->keyBy('date');

        // 2. HOUR-BY-HOUR MATHEMATICAL LOGIC
        foreach ($consumptions as $consumption) {
            $date = $consumption->date;
            $priceRow = $pricesByDate->get($date);

            for ($h = 1; $h <= 25; $h++) {
                $hourKey = "h{$h}";

                // Skip the hour if data is missing
                if (is_null($consumption->$hourKey) || is_null($priceRow?->$hourKey)) {
                    continue;
                }

                $consumo_hora = (double) $consumption->$hourKey;
                $omie_val = (double) $priceRow->$hourKey;

                $evaluatedFormula = str_replace('[OMIE_MD]', $omie_val, $formula);

                // Strict sanitization before mathematical evaluation
                if (preg_match('/[^0-9\+\-\*\/\(\)\.\s]/', $evaluatedFormula)) {
                    throw new Exception('Invalid characters in formula', 400);
                }

                // Price calculation by safely evaluating the mathematical string
                try {
                    $precio_evaluado = eval("return {$evaluatedFormula};");
                } catch (Exception $e) {
                    throw new Exception('Mathematical syntax error', 500);
                }

                $totalImporte += ($precio_evaluado * $consumo_hora);
                $totalConsumo += $consumo_hora;
            }
        }

        if ($totalConsumo == 0) {
            throw new Exception('Zero consumption', 400);
        }

        return $totalImporte / $totalConsumo;
    }
}
