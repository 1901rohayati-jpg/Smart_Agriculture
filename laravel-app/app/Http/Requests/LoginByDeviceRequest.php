<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginByDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:100'],
        ];
    }
}
