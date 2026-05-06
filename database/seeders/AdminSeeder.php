<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void{
        // 🔐 ADMIN PRINCIPAL
        Admin::create([
            'id' => Str::uuid(),
            'nom' => 'Travel Babi',
            'numero' => '0140834533',
            'email' => 'Jeedtech@gmail.com',
            'password' => Hash::make('Lafamillekohou2024'),
            'role' => 'admin',
            'solde' => 0
        ]);

        // 👤 SOUS ADMIN 1
        Admin::create([
            'id' => Str::uuid(),
            'nom' => 'Emmanuel Bamidele',
            'numero' => '0140022693',
            'email' => 'marcbamidele@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'sous_admin',
            'solde' => 5
        ]);

        // 👤 SOUS ADMIN 2
        Admin::create([
            'id' => Str::uuid(),
            'nom' => 'Eloge Kohou',
            'numero' => '0140834531',
            'email' => 'angeeloge8@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'sous_admin',
            'solde' => 3
        ]);

        // 👤 SOUS ADMIN 3
        Admin::create([
            'id' => Str::uuid(),
            'nom' => 'Judicael Cakpo',
            'numero' => '0564624366',
            'email' => 'chrisjudiv@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'sous_admin',
            'solde' => 2
        ]);

        // 👤 SOUS ADMIN 4
        Admin::create([
            'id' => Str::uuid(),
            'nom' => 'David Kouaho',
            'numero' => '0171136261',
            'email' => 'kouahodavid6@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'sous_admin',
            'solde' => 8
        ]);
    }
}