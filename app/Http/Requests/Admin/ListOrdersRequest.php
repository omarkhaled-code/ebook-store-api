<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(OrderStatus::values())],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
