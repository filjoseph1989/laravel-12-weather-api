<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WeatherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => 'sometimes|string|regex:/^[a-zA-Z\s-]+$/|max:20',
            'country' => 'sometimes|string|regex:/^[A-Z]+$/u|max:2',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'city.string' => 'City must be a string.',
            'city.max' => 'City must not be greater than 20 characters.',
            'city.regex' => 'City must only contain letters, spaces, and hyphens.',
            'country.string' => 'Country must be a string.',
            'country.max' => 'Country must not be greater than 2 characters.',
            'country.regex' => 'Country must only contain uppercase letters.',
            'country.required' => 'Country is required.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('city') === null && $this->input('country') !== null) {
                $validator->errors()->add('city', 'City is required when country is provided.');
            }
            if ($this->input('city') !== null && strlen($this->input('city')) < 3) {
                $validator->errors()->add('city', 'City must be at least 3 characters long.');
            }
            if ($this->input('country') !== null && strlen($this->input('country')) !== 2) {
                $validator->errors()->add('country', 'Country must be exactly 2 uppercase letters.');
            }
        });
    }
}
