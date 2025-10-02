<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required|integer',
            'row_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'color' => 'required|string',
            'is_deadline' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'links' => 'nullable|string',
        ];
    }
}
