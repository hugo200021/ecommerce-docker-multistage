<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('ecommerce.admin.email');
        $password = config('ecommerce.admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            return;
        }

        $user = User::updateOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'name' => config('ecommerce.admin.name', 'Administrador'),
                'password' => Hash::make($password),
            ]
        );

        $user->forceFill(['role' => 'admin'])->save();
    }
}
