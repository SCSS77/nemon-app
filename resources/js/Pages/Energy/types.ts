export interface MetricRow {
  id: number;
  date: string;
  [key: string]: string | number;
}

export interface CalculationResponse {
  price_indexed: number;
}

export interface ApiErrorResponse {
  error: string;
}
