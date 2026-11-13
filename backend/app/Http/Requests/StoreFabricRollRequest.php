<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFabricRollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'roll_number' => 'required|string|unique:fabric_rolls,roll_number|max:100',
            'lot_number' => 'required|string|max:100',
            'fabric_type' => 'required|string|max:100',
            'buyer' => 'nullable|string|max:100',
            'order_number' => 'nullable|string|max:100',
            'stage' => 'required|string|max:50',
            'roll_length' => 'nullable|numeric|min:0',
            'test_date' => 'required|date',
            'tested_by' => 'nullable|string|max:100',
            'length_reading_1' => 'required|numeric|between:-50,50',
            'length_reading_2' => 'required|numeric|between:-50,50',
            'length_reading_3' => 'required|numeric|between:-50,50',
            'width_reading_1' => 'required|numeric|between:-50,50',
            'width_reading_2' => 'required|numeric|between:-50,50',
            'width_reading_3' => 'required|numeric|between:-50,50',
            'notes' => 'nullable|string',
        ];
    }
}

