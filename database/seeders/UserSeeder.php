<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin')
        ])->assignRole('admin');
        User::create([
            'name' => 'Worker',
            'email' => 'worker@gmail.com',
            'password' => bcrypt('worker')
        ])->assignRole('worker');
        User::create([
            'name' => 'Member',
            'email' => 'member@gmail.com',
            'password' => bcrypt('member')
        ])->assignRole('member');
        User::create([
            'name' => 'user',
            'email' => 'user@gmail.com',
            'password' => bcrypt('user')
        ])->assignRole('user');
    }
}
