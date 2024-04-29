<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "username" => ["required", "email", "max:100"],
            "password" => ["required", Password::min(6)->letters()->numbers()->symbols()]
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            "username" => strtolower($this->input("username"))
        ]);
    }

    public function passedValidation(): void
    {
        $this->merge([
            "password" => bcrypt($this->input("password"))
        ]);
    }
}
