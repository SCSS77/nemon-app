<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Energy Consumption & Price Indexed Calculator

A production-ready Single-Page Monolith (SPA) built with **Laravel**, **Inertia.js**, **Vue 3 (Composition API)**, and **TypeScript**. This application computes the hourly weighted indexed price over dynamic date ranges using electricity consumption data and OMIE market prices.

---

## Architectural Overview & Design Patterns

To prevent the common pitfall of *Smart Controllers* and ensure maximum scalability, the application is strictly structured using decoupled, testable architectural layers on both the Backend and Frontend.

### Backend (PHP)
- **Form Request Layer (`CalculateEnergyRequest`)**: Intercepts HTTP requests, sanitizes inputs, enforces strict `Y-m-d` date validation, and overrides the default validation response to comply with standard API status code specs (`400 Bad Request` instead of `422`).
- **Domain Business Action Layer (`CalculatePriceIndexedAction`)**: A pure Service/Action class containing the core mathematical business logic. It handles data retrieval via Eloquent and processes the 25-hour utility period safely.
- **Security Sanitization**: Incorporates strict RegEx validation before string evaluation (`eval()`), fully shielding the application from remote code injection attempts.

### Frontend (Vue 3 + TypeScript)
- **Type Safety Layer (`types.ts`)**: Enforces strict typing for dynamic hourly curves (`h1` through `h25`) and API responses.
- **Service/Composable Layer (`useEnergyCalculator.ts`)**: Extracts async network traffic, Axios operations, and reactive state management away from the UI templates.
- **UI/View Layer (`Dashboard.vue`)**: A clean utility-first layout engineered via **Tailwind CSS v4** focused exclusively on layout composition and user reactivity.

---

## Energy Industry Context (The 25th Hour Handling)

In the electrical utility business, standard days contain 24 hours, but daylight saving transitions require flexibility:
- **Spring Transition (23-Hour Day)**: Missing hours inherently fetch `null` from the database. The system handles this seamlessly without syntax crashes.
- **Autumn Transition (25-Hour Day)**: The database migrations provide columns up to `h25`. The Action iterates natively through 25 iterations, calculating true weighted averages rather than unweighted flat averages—protecting commercial margins.

Furthermore, the database migrations apply a **`unique()` B-Tree index** on the `date` columns across both tables, assuring optimal lookup query times when scanning massive chronological loads.

---

## Installation & Docker Local Setup

Ensure you have **Docker Desktop** running on your environment before proceeding.

1. **Clone the repository and enter the project folder:**
   ```bash
   cd nemon-app
   ```

2. **Spin up the Docker Containers (Detached Mode):**
   ```bash
   docker compose up -d
   ```

3. **Install Backend Dependencies & Set Up Database Schema:**
   ```bash
   docker compose exec web composer install
   docker compose exec web php artisan migrate
   docker compose exec web php artisan db:seed
   ```

4. **Install Frontend Dependencies & Run Vite Live Server:**
   ```bash
   npm install
   npm run dev
   ```

5. **Access the application:**
   Open your browser and navigate to **`http://localhost`**.

---

## Automated Testing Suite

The project includes isolated unit and integration testing pipelines across both ecosystems.

### Running Backend Integration Tests (PHPUnit)
Leverages an isolated database environment inside Docker to trace transactional query evaluation and failure states:
```bash
docker compose exec web php artisan test --filter=CalculatePriceIndexedActionTest
```

### Running Frontend Service Tests (Vitest)
Executes rapid unit assertions on the decoupled Composable state and mocks network dependencies:
```bash
npx vitest run
```

---

## Production Improvements (Next Steps)
While this iteration is optimized for evaluation, a true high-traffic cloud environment would expand upon:
1. **API Token Authentication**: Wrapping the route with `auth:sanctum` and adding strict API rate limiting (`throttle` middleware) against Denial of Service (DoS) events.
2. **Safe Math Compilers**: Substituting `eval()` with `symfony/expression-language` for parsing sophisticated pricing brackets containing nested formulas (`MIN`, `MAX`, `IF`).
