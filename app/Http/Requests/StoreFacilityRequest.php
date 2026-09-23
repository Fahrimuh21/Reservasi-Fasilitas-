<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreFacilityRequest – Validasi untuk menambah fasilitas baru.
 *
 * Digunakan oleh: POST /api/admin/facilities
 * Status baru selalu dimulai 'pending' dan menunggu persetujuan Petugas.
 */
class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani oleh middleware role:admin
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code'             => 'required|string|max:50|unique:facilities,code',
            'name'             => 'required|string|max:255',
            'facility_type_id' => 'required|exists:facility_types,id',
            'location_id'      => 'required|exists:locations,id',
            'capacity'         => 'required|integer|min:1',
            'description'      => 'nullable|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required'             => 'Kode fasilitas wajib diisi.',
            'code.unique'               => 'Kode fasilitas sudah digunakan.',
            'code.max'                  => 'Kode fasilitas maksimal 50 karakter.',
            'name.required'             => 'Nama fasilitas wajib diisi.',
            'name.max'                  => 'Nama fasilitas maksimal 255 karakter.',
            'facility_type_id.required' => 'Tipe fasilitas wajib dipilih.',
            'facility_type_id.exists'   => 'Tipe fasilitas tidak valid.',
            'location_id.required'      => 'Lokasi wajib dipilih.',
            'location_id.exists'        => 'Lokasi tidak valid.',
            'capacity.required'         => 'Kapasitas wajib diisi.',
            'capacity.integer'          => 'Kapasitas harus berupa angka.',
            'capacity.min'              => 'Kapasitas minimal 1.',
        ];
    }
}
