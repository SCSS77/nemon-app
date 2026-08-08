<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\CalculatePriceIndexedAction;
use App\Models\Consumption;
use App\Models\Price;
use Exception;

class CalculatePriceIndexedActionTest extends TestCase
{
    /**
     * Test that the weighted indexed price calculation computes the 25 hours correctly.
     */
    public function test_it_calculates_the_weighted_indexed_price_correctly(): void
    {
        // 1. ARRANGEMENT
        $date = '2025-03-01';
        
        // Clean up any existing records for this specific date before starting
        Consumption::where('date', $date)->delete();
        Price::where('date', $date)->delete();

        // Setup flat consumption metrics of 2.0 units for all 25 hours
        $consumptionData = ['date' => $date];
        for ($h = 1; $h <= 25; $h++) { 
            $consumptionData["h{$h}"] = 2.0; 
        }
        Consumption::create($consumptionData);

        // Setup flat OMIE price metrics of 0.1 units for all 25 hours
        $priceData = ['date' => $date];
        for ($h = 1; $h <= 25; $h++) { 
            $priceData["h{$h}"] = 0.1; 
        }
        Price::create($priceData);

        // Target Formula: ([OMIE_MD] * 1.1) + 2.5 => (0.1 * 1.1) + 2.5 = 2.61
        $formula = '([OMIE_MD] * 1.1) + 2.5';

        // 2. ACT
        $action = new CalculatePriceIndexedAction();
        $result = $action->execute($date, $date, $formula);

        // 3. ASSERT
        $this->assertEquals(2.61, round($result, 2));

        // Clean up database records after running the integration test
        Consumption::where('date', $date)->delete();
        Price::where('date', $date)->delete();
    }

    /**
     * Test that a 404 Exception is thrown if data records are missing.
     */
    public function test_it_throws_a_404_exception_if_historical_records_are_missing(): void
    {
        // 1. EXPECTATION
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Missing data');

        // 2. ACT
        $action = new CalculatePriceIndexedAction();
        $action->execute('2027-01-01', '2027-01-01', '[OMIE_MD]');
    }
}
