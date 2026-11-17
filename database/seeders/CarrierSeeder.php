<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'Maersk Line',
                'code' => 'MAEU',
                'contact_email' => 'service@maersk.com',
                'contact_phone' => '+62-21-5299-1000',
                'service_type' => 'sea',
                'is_active' => true,
            ],
            [
                'name' => 'Samudera Indonesia',
                'code' => 'SMDR',
                'contact_email' => 'cs@samudera.id',
                'contact_phone' => '+62-21-5696-2000',
                'service_type' => 'multimodal',
                'is_active' => true,
            ],
            [
                'name' => 'DHL Global Forwarding',
                'code' => 'DHL',
                'contact_email' => 'support@dhl.co.id',
                'contact_phone' => '+62-21-7917-8000',
                'service_type' => 'air',
                'is_active' => true,
            ],
            [
                'name' => 'FedEx Express Freight',
                'code' => 'FDX',
                'contact_email' => 'freight@fedex.com',
                'contact_phone' => '+62-21-7599-8800',
                'service_type' => 'air',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $carrier) {
            Carrier::firstOrCreate(
                ['code' => $carrier['code']],
                $carrier
            );
        }
    }
}
