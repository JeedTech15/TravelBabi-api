<?php

namespace Database\Seeders;

use App\Models\Admin;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'id' => Str::uuid(),
            'nom' => 'Eloge Kohou',
            'numero' => '0140834531',
            'image' =>  'dkjnfkjdsnfkds',
            'email' => 'angeeloge8@gmail.com',
            'password' => Hash::make('Lafamillekohou2024'),
            'role' => 'admin'      
        ]);
    }
}
