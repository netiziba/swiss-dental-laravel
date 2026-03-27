<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Professionelle Zahnreinigung', 'description' => 'Gründliche Reinigung und Politur Ihrer Zähne', 'duration_minutes' => 60, 'price_chf' => 180.00, 'category' => 'Prophylaxe'],
            ['name' => 'Kontrolluntersuchung', 'description' => 'Regelmässige Kontrolluntersuchung mit Befund', 'duration_minutes' => 30, 'price_chf' => 120.00, 'category' => 'Allgemein'],
            ['name' => 'Zahnfüllung', 'description' => 'Kompositfüllung bei Karies', 'duration_minutes' => 45, 'price_chf' => 250.00, 'category' => 'Restauration'],
            ['name' => 'Wurzelbehandlung', 'description' => 'Endodontische Behandlung', 'duration_minutes' => 90, 'price_chf' => 800.00, 'category' => 'Endodontie'],
            ['name' => 'Zahnextraktion', 'description' => 'Schonende Zahnentfernung', 'duration_minutes' => 45, 'price_chf' => 350.00, 'category' => 'Chirurgie'],
            ['name' => 'Bleaching', 'description' => 'Professionelles Zahnbleaching', 'duration_minutes' => 60, 'price_chf' => 450.00, 'category' => 'Ästhetik'],
            ['name' => 'Implantat-Beratung', 'description' => 'Beratungsgespräch für Zahnimplantate', 'duration_minutes' => 45, 'price_chf' => 150.00, 'category' => 'Implantologie'],
            ['name' => 'Zahnkrone', 'description' => 'Keramikkrone Anfertigung und Einsetzung', 'duration_minutes' => 60, 'price_chf' => 1200.00, 'category' => 'Prothetik'],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service['name']], $service);
        }
    }
}
