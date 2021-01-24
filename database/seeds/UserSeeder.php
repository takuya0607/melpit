<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'test1',
            'email' => 'test1@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
        ]);
    }
}
