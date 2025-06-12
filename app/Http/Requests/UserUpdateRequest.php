<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->user->id,
            'password' => 'sometimes|string|min:8',
            'national_id' => 'sometimes|string|max:11|unique:users,national_id,' . $this->user->id,
            'country' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'district' => 'sometimes|string|max:100',
            'currency' => 'sometimes|string|size:3',
            'phone' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,other',
            'address' => 'sometimes|string|max:500',
            'postal_code' => 'sometimes|string|max:10',
        ];
    }

       /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already taken.',
            'national_id.unique' => 'This national ID is already registered.',
            'currency.size' => 'Currency code must be exactly 3 characters.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'gender.in' => 'Gender must be male, female, or other.',
        ];
    }
}
