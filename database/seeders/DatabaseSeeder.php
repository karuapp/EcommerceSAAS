<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('migrate');
        Artisan::call('package:migrate LandingPage');
        Artisan::call('package:seed LandingPage');
        $this->call(AddonSeeder::class);
        $this->call(PlansTableSeeder::class);
        $this->call(PermissionTableSeeder::class);       
        $this->call(DefaultSetting::class);
        $this->call(AiTemplateSeeder::class);
    }
}
