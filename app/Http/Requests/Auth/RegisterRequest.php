<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // Who is allowed to make this request?
    // true = everyone (it's a public route)
    public function authorize(): bool
    {
        return true;
    }

    // Validation rules
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['sometimes', 'string', 'in:user,admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    // Custom error messages
    public function messages(): array
    {
        return [
            'name.required'      => 'Please enter your name.',
            'email.required'     => 'Please enter your email.',
            'email.unique'       => 'This email is already registered.',
            'password.required'  => 'Please enter a password.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}