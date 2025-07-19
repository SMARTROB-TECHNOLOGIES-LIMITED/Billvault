<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NINRequest extends FormRequest
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
            'nin'          => 'required|digits:11',
            'selfie_image' => 'required|string', // Base64
            'address'      => 'required|string',
            'zipcode'      => 'required|string',
        ];
    }
}
