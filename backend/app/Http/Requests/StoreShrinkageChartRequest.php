<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShrinkageChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_name' => 'required|string|max:100',
            'group_code' => 'nullable|string|max:50',
            'stage' => 'required|string|max:50',
            'fabric_type' => 'nullable|string|max:100',
            'length_min' => 'required|numeric|between:-50,50',
            'length_max' => 'required|numeric|between:-50,50',
            'width_min' => 'required|numeric|between:-50,50',
            'width_max' => 'required|numeric|between:-50,50',
            'tolerance' => 'nullable|numeric|min:0',
            'pattern_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}

