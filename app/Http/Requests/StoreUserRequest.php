<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk Admin menambah akun User atau Officer langsung (FR-5, FR-6).
 *
 * Bedanya dari RegisterRequest:
 * - Admin memilih role secara eksplisit ('user' atau 'officer')
 * - Akun langsung 'active', tidak perlu melalui pending
 * - user_type nullable (officer tidak perlu user_type)
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya bisa dipanggil dari route yang sudah dilindungi middleware role:admin
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            // Admin memilih role eksplisit – 'user' atau 'officer' (bukan 'admin' untuk keamanan)
            'role'      => ['required', 'string', Rule::in(['user', 'officer'])],
            // user_type wajib diisi saat role = user (mahasiswa/dosen/staf)
            'user_type' => [
                Rule::requiredIf(fn () => $this->input('role') === 'user'),
                'nullable',
                'string',
                Rule::in(['mahasiswa', 'dosen', 'staf']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.unique'       => 'Email ini sudah terdaftar dalam sistem.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'role.required'      => 'Role wajib dipilih.',
            'role.in'            => 'Role harus salah satu dari: user, officer.',
            'user_type.required_if' => 'Jenis pengguna wajib diisi untuk role user.',
            'user_type.in'       => 'Jenis pengguna harus salah satu dari: mahasiswa, dosen, staf.',
        ];
    }
}
