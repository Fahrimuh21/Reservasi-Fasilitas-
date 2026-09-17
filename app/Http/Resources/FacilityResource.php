<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * FacilityResource – Format JSON untuk fasilitas.
 *
 * Mendukung dua mode tampilan:
 * - List (relasi tidak di-load): type & location ditampilkan sebagai string sederhana
 * - Detail (relasi di-load): type & location ditampilkan sebagai objek lengkap
 *
 * Juga menyertakan 'availability_today' jika tersedia (diisi oleh controller).
 */
class FacilityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'name'        => $this->name,
            'type'        => $this->relationLoaded('facilityType')
                ? new FacilityTypeResource($this->facilityType)
                : optional($this->facilityType)->name,
            'location'    => $this->relationLoaded('location')
                ? new LocationResource($this->location)
                : optional($this->location)->building,
            'capacity'    => $this->capacity,
            'description' => $this->when(
                $request->routeIs('*show*') || $request->is('*/facilities/*'),
                $this->description
            ),
            'status'      => $this->status,
            'availability_today' => $this->when(
                isset($this->availability_today),
                $this->availability_today
            ),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
