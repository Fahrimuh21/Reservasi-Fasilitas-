<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'                => fake()->name(),
            'email'               => fake()->unique()->safeEmail(),
            'email_verified_at'   => now(),
            'password'            => static::$password ??= Hash::make('password'),
            'role'                => 'user',
            'account_status'      => 'active',
            'user_type'           => fake()->randomElement(['mahasiswa', 'dosen', 'staf']),
            'registration_source' => 'self_register',
            'remember_token'      => Str::random(10),
        ];
    }

    /** State: akun pending (menunggu verifikasi) */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => 'pending',
        ]);
    }

    /** State: akun suspended/nonaktif */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => 'suspended',
        ]);
    }

    /** State: petugas (dibuat admin) */
    public function officer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'                => 'officer',
            'user_type'           => null,
            'registration_source' => 'admin_created',
            'account_status'      => 'active',
        ]);
    }

    /** State: admin */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'                => 'admin',
            'user_type'           => null,
            'registration_source' => 'admin_created',
            'account_status'      => 'active',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

