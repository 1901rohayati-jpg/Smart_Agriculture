<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualTriggerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duration_sec' => ['required', 'integer', 'min:1', 'max:60'],
        ];
    }
}
