<?php

namespace App\Http\Requests\Ebook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListEbooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:100'],
            'author' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['latest', 'price_asc', 'price_desc', 'title'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
