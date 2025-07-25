<?php

namespace App\Http\Requests\HR\DepartureReason;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepartureReasonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|unique:hr_departure_reasons'
        ];
    }

    /**
     * Validation Messages
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.custom.departureReason.name.required'),
            'name.unique' => __('validation.custom.departureReason.name.unique')
        ];
    }
}
