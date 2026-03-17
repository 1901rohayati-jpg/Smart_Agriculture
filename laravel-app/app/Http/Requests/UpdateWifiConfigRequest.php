<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWifiConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ssid' => ['required', 'string', 'max:64'],
            'password' => ['required', 'string', 'max:64'],
        ];
    }
}
