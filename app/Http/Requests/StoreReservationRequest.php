<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We use role middleware for this
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_id' => [
                'required',
                'exists:facilities,id'
            ],
            'start_at' => [
                'required',
                'date'
            ],
            'end_at' => [
                'required',
                'date',
                'after:start_at'
            ],
            'purpose' => [
                'required',
                'string',
                'max:1000'
            ],
        ];
    }
}
