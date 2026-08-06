<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CalculateEnergyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We allow the request
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            'formula' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!str_contains($value, '[OMIE_MD]')) {
                        $fail('The formula must contain the [OMIE_MD] segment.');
                    }
                },
            ],
        ];
    }

    /**
     * Forces Laravel to return a JSON response with a 400 (Bad Request) status code
     * instead of the default 422 code.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['error' => $validator->errors()->first()], 400)
        );
    }
}
