<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk Registrasi Mandiri Pengguna (FR-1).
 *
 * Sesuai DESIGN.md Bagian 14 (Validasi Laravel) & PRD FR-1.
 * Validasi server-side wajib sesuai Ketentuan Umum poin 2.
 * Role di-set otomatis 'user' – tidak boleh dipilih user di form.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Endpoint registrasi terbuka untuk publik (guest)
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Minimal 8 karakter – asumsi tim (SRS tidak menentukan, sesuai PRD Bagian 13)
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            // Jenis pengguna kampus – wajib saat self-register (FR-1)
            'user_type' => ['required', 'string', 'in:mahasiswa,dosen,staf'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'user_type.required' => 'Jenis pengguna wajib dipilih (mahasiswa/dosen/staf).',
            'user_type.in'       => 'Jenis pengguna harus salah satu dari: mahasiswa, dosen, staf.',
        ];
    }
}
