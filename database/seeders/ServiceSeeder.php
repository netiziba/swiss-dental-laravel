<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $sole = Service::updateOrCreate(
            ['name' => 'Kontrolluntersuchung'],
            [
                'description' => 'Regelmässige Kontrolluntersuchung mit Befund',
                'duration_minutes' => 60,
                'price_chf' => 100.00,
                'category' => 'Allgemein',
                'is_active' => true,
            ]
        );

        Service::where('id', '!=', $sole->id)->delete();
    }
}
