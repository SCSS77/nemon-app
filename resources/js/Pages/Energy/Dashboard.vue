<script setup lang="ts">
import { useEnergyCalculator } from './useEnergyCalculator';
import type { MetricRow } from './types';

// 1. PROPS LAYER
const props = defineProps<{
  consumptions: MetricRow[];
  prices: MetricRow[];
}>();

// 2. LOGISTICS LAYER
const { 
  startDate, 
  endDate, 
  formula, 
  priceIndexed, 
  errorMessage, 
  isLoading, 
  calculate 
} = useEnergyCalculator();

// Static hours header generator to build the table columns dynamically (h1 to h25)
const hours = Array.from({ length: 25 }, (_, i) => `h${i + 1}`);
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto space-y-6">
      
      <header class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800">Energy Consumption & Price Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Calculate indexed prices and audit historical data metrics hour by hour.</p>
      </header>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800 mb-5 tracking-tight">Indexed Price Calculator</h2>
          
          <form @submit.prevent="calculate" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Start Date</label>
                <input 
                  v-model="startDate" 
                  type="date" 
                  required
                  class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-700 text-sm transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">End Date</label>
                <input 
                  v-model="endDate" 
                  type="date" 
                  required
                  class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-700 text-sm transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Mathematical Formula</label>
              <input 
                v-model="formula" 
                type="text" 
                required
                placeholder="e.g. ([OMIE_MD] * 1.1) + 2.5"
                class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-800 text-sm font-mono tracking-wide transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none"
              />
              <p class="text-xs text-gray-400 mt-2 flex items-center gap-1 flex-wrap">
                <span>The formula tag</span>
                <span class="font-mono bg-blue-50 text-blue-600 font-semibold px-1.5 py-0.5 rounded text-[11px] border border-blue-100">[OMIE_MD]</span>
                <span>is mandatory and will be replaced dynamically per hour.</span>
              </p>
            </div>

            <button 
              type="submit" 
              :disabled="isLoading"
              class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-md shadow-sm transition-all duration-150 text-sm disabled:opacity-50 tracking-wide mt-2"
            >
              {{ isLoading ? 'Computing...' : 'Calculate Indexed Price' }}
            </button>
          </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-center">
          <h2 class="text-lg font-semibold text-gray-700 mb-2 text-center">Calculation Output</h2>
          
          <div v-if="priceIndexed !== null" class="text-center p-6 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Final Indexed Price</p>
            <p class="text-4xl font-extrabold text-green-600 mt-2">{{ priceIndexed }} <span class="text-lg font-medium">€/MWh</span></p>
          </div>

          <div v-else-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <p class="font-semibold mb-1">Error detected:</p>
            <p class="text-xs font-mono">{{ errorMessage }}</p>
          </div>

          <div v-else class="text-center text-gray-400 py-8">
            <p class="text-sm">Fill the fields and submit the calculation request to view metrics here.</p>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-700">Historical Consumption Records</h3>
          </div>
          <div class="overflow-x-auto max-h-72">
            <table class="w-full text-left text-xs whitespace-nowrap">
              <thead class="bg-gray-100 text-gray-600 uppercase sticky top-0">
                <tr>
                  <th class="p-3 border-b">Date</th>
                  <th v-for="h in hours" :key="h" class="p-3 border-b text-center">{{ h }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-600">
                <tr v-for="item in props.consumptions" :key="item.id" class="hover:bg-gray-50">
                  <td class="p-3 font-medium bg-gray-50 sticky left-0 shadow-sm">{{ item.date }}</td>
                  <td v-for="h in hours" :key="h" class="p-3 text-center font-mono">{{ item[h] ?? '-' }}</td>
                </tr>
                <tr v-if="props.consumptions.length === 0">
                  <td :colspan="26" class="p-8 text-center text-gray-400">No consumption metrics found in the database.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-700">Historical Price Records (OMIE)</h3>
          </div>
          <div class="overflow-x-auto max-h-72">
            <table class="w-full text-left text-xs whitespace-nowrap">
              <thead class="bg-gray-100 text-gray-600 uppercase sticky top-0">
                <tr>
                  <th class="p-3 border-b">Date</th>
                  <th v-for="h in hours" :key="h" class="p-3 border-b text-center">{{ h }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-600">
                <tr v-for="item in props.prices" :key="item.id" class="hover:bg-gray-50">
                  <td class="p-3 font-medium bg-gray-50 sticky left-0 shadow-sm">{{ item.date }}</td>
                  <td v-for="h in hours" :key="h" class="p-3 text-center font-mono text-amber-600">{{ item[h] ?? '-' }}</td>
                </tr>
                <tr v-if="props.prices.length === 0">
                  <td :colspan="26" class="p-8 text-center text-gray-400">No historical price metrics found in the database.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>
