<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ebook_id' => ['required', 'integer', 'exists:ebooks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ebook_id.required' => 'Please select an ebook.',
            'ebook_id.exists'   => 'This ebook does not exist.',
        ];
    }
}