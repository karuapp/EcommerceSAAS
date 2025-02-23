<?php

namespace Workdo\LandingPage\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Workdo\LandingPage\Entities\LandingPageSetting;

class LandingPageDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $this->call(LandingPageDataTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(OwnerMenusTableSeeder::class);
    }
}
