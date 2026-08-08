import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useEnergyCalculator } from '../useEnergyCalculator';
import axios from 'axios';

// Mock Axios to intercept and simulate API network requests safely
vi.mock('axios');

describe('useEnergyCalculator Composable', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should initialize with accurate default energy industry states', () => {
    const { startDate, endDate, formula, priceIndexed, errorMessage, isLoading } = useEnergyCalculator();

    expect(startDate.value).toBe('');
    expect(endDate.value).toBe('');
    expect(formula.value).toBe('([OMIE_MD] * 1.1) + 2.5');
    expect(priceIndexed.value).toBeNull();
    expect(errorMessage.value).toBeNull();
    expect(isLoading.value).toBe(false);
  });

  it('should process successful API calculation responses smoothly', async () => {
    const { startDate, endDate, formula, calculate, priceIndexed, errorMessage, isLoading } = useEnergyCalculator();
    
    startDate.value = '2025-03-01';
    endDate.value = '2025-03-01';
    formula.value = '([OMIE_MD] * 0.6) + 0.88';

    // Simulate a successful 200 OK API response from Laravel
    vi.mocked(axios.post).mockResolvedValueOnce({ 
      data: { price_indexed: 2.61 } 
    });

    await calculate();

    expect(priceIndexed.value).toBe(2.61);
    expect(isLoading.value).toBe(false);
    expect(errorMessage.value).toBeNull();
  });

  it('should catch and handle business logic API validation errors gracefully', async () => {
    const { startDate, calculate, priceIndexed, errorMessage, isLoading } = useEnergyCalculator();
    
    startDate.value = '2025-03-01'; // Missing end_date on purpose

    // Simulate a 400 Bad Request error returned by our Form Request
    vi.mocked(axios.post).mockRejectedValueOnce({
      response: {
        data: { error: 'The end date field is required.' }
      }
    });

    await calculate();

    expect(priceIndexed.value).toBeNull();
    expect(isLoading.value).toBe(false);
    expect(errorMessage.value).toBe('The end date field is required.');
  });
});
