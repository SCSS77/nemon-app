import { ref } from 'vue';
import axios from 'axios';
import type { CalculationResponse } from './types';

export function useEnergyCalculator() {
  const startDate = ref<string>('');
  const endDate = ref<string>('');
  const formula = ref<string>('([OMIE_MD] * 1.1) + 2.5');
  const priceIndexed = ref<number | null>(null);
  const errorMessage = ref<string | null>(null);
  const isLoading = ref<boolean>(false);

  const calculate = async () => {
    isLoading.value = true;
    errorMessage.value = null;
    priceIndexed.value = null;

    try {
      const response = await axios.post<CalculationResponse>('/api/calculate', {
        start_date: startDate.value,
        end_date: endDate.value,
        formula: formula.value,
      });

      priceIndexed.value = response.data.price_indexed;
    } catch (error: any) {
      if (error.response?.data?.error) {
        errorMessage.value = error.response.data.error;
      } else {
        errorMessage.value = 'An unexpected error occurred during calculation.';
      }
    } finally {
      isLoading.value = false;
    }
  };

  return {
    startDate,
    endDate,
    formula,
    priceIndexed,
    errorMessage,
    isLoading,
    calculate,
  };
}
