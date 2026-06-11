<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'email_verified_at' => ['nullable'],
            'password' => ['required', Password::min(8)],
            'role' => ['required', Rule::in('admin', 'doctor', 'pasien')],
            'remember_token' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['required', 'date']
        ];
    }
  

    public function messages()
    {
        return [
            'birth_date.required' => 'Tanggal lahir wajib diisi',
            'birth_date.date'     => 'Format tanggal lahir tidak valid',
            'birth_date.before'   => 'Tanggal lahir harus sebelum hari ini',
            'password.min'        => 'Password minimal 8 karakter',
            'role.in'             => 'Role yang dipilih tidak valid',
        ];
    }
}
