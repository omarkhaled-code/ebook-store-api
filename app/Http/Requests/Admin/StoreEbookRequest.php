<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'author' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:51200'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
