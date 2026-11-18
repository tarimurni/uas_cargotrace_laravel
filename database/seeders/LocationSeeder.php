<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Pelabuhan Tanjung Priok',
                'code' => 'IDTPP',
                'type' => 'seaport',
                'city' => 'Jakarta Utara',
                'country' => 'Indonesia',
                'latitude' => -6.1017400,
                'longitude' => 106.8837700,
            ],
            [
                'name' => 'Terminal Kargo Soekarno-Hatta (CGK)',
                'code' => 'IDCGK',
                'type' => 'airport',
                'city' => 'Tangerang',
                'country' => 'Indonesia',
                'latitude' => -6.1275000,
                'longitude' => 106.6537000,
            ],
            [
                'name' => 'Pelabuhan Tanjung Perak',
                'code' => 'IDSUB',
                'type' => 'seaport',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
                'latitude' => -7.1994000,
                'longitude' => 112.7336000,
            ],
            [
                'name' => 'Pelabuhan Belawan',
                'code' => 'IDBLW',
                'type' => 'seaport',
                'city' => 'Medan',
                'country' => 'Indonesia',
                'latitude' => 3.7828000,
                'longitude' => 98.6942000,
            ],
            [
                'name' => 'Port of Singapore (PSA)',
                'code' => 'SGSIN',
                'type' => 'seaport',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'latitude' => 1.2644000,
                'longitude' => 103.8400000,
            ],
            [
                'name' => 'Port of Tanjung Pelepas',
                'code' => 'MYTPP',
                'type' => 'seaport',
                'city' => 'Johor Bahru',
                'country' => 'Malaysia',
                'latitude' => 1.3653000,
                'longitude' => 103.5506000,
            ],
            [
                'name' => 'Port of Rotterdam',
                'code' => 'NLRTM',
                'type' => 'seaport',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'latitude' => 51.9566000,
                'longitude' => 4.1458000,
            ],
            [
                'name' => 'Port of Shanghai',
                'code' => 'CNSHA',
                'type' => 'seaport',
                'city' => 'Shanghai',
                'country' => 'China',
                'latitude' => 31.2304000,
                'longitude' => 121.4737000,
            ],
            [
                'name' => 'Port of Los Angeles',
                'code' => 'USLAX',
                'type' => 'seaport',
                'city' => 'Los Angeles',
                'country' => 'United States',
                'latitude' => 33.7432000,
                'longitude' => -118.2673000,
            ],
            [
                'name' => 'Port of Melbourne',
                'code' => 'AUMEL',
                'type' => 'seaport',
                'city' => 'Melbourne',
                'country' => 'Australia',
                'latitude' => -37.8136000,
                'longitude' => 144.9631000,
            ],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['code' => $location['code']],
                $location
            );
        }
    }
}
