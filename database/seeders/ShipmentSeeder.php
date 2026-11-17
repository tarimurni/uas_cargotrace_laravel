<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->first()
            ?? User::where('role', 'customer')->first();
        $operator = User::where('email', 'operator@cargotrace.com')->first()
            ?? User::where('role', 'operator')->first();

        $maersk = Carrier::where('code', 'MAEU')->first();
        $samudera = Carrier::where('code', 'SMDR')->first();
        $dhl = Carrier::where('code', 'DHL')->first();
        $fedex = Carrier::where('code', 'FDX')->first();

        $tanjungPriok = Location::where('code', 'IDTPP')->first();
        $cgkAirport = Location::where('code', 'IDCGK')->first();
        $tanjungPerak = Location::where('code', 'IDSUB')->first();
        $belawan = Location::where('code', 'IDBLW')->first();
        $singapore = Location::where('code', 'SGSIN')->first();
        $tanjungPelepas = Location::where('code', 'MYTPP')->first();
        $rotterdam = Location::where('code', 'NLRTM')->first();

        // Shipment 1: Delivered
        $s1 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-001'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $maersk->id,
                'origin_id' => $tanjungPriok->id,
                'destination_id' => $singapore->id,
                'cargo_type' => 'Electronics & High-Tech Components',
                'weight' => 4850.50,
                'volume' => 14.20,
                'expected_departure' => Carbon::parse('2026-11-10 08:00:00'),
                'expected_arrival' => Carbon::parse('2026-11-14 18:00:00'),
                'estimated_arrival' => Carbon::parse('2026-11-14 18:00:00'),
                'actual_departure' => Carbon::parse('2026-11-10 09:30:00'),
                'actual_arrival' => Carbon::parse('2026-11-14 17:15:00'),
                'current_status' => 'delivered',
                'is_delayed' => false,
                'delay_hours' => 0,
                'notes' => 'Priority sea freight with temperature-controlled container.',
            ]
        );
        $s1->items()->createMany([
            ['item_name' => 'Printed Circuit Boards (PCBs)', 'item_code' => 'PCB-2044', 'quantity' => 250, 'weight' => 1200.00, 'volume' => 3.50, 'declared_value' => 45000.00],
            ['item_name' => 'Lithium-Ion Battery Packs (UN3480)', 'item_code' => 'BAT-LIO-88', 'quantity' => 100, 'weight' => 1650.50, 'volume' => 4.20, 'declared_value' => 32000.00],
            ['item_name' => 'Semiconductor Sensor Modules', 'item_code' => 'SENS-IC-01', 'quantity' => 500, 'weight' => 2000.00, 'volume' => 6.50, 'declared_value' => 68000.00],
        ]);
        $s1->trackingEvents()->createMany([
            ['location_id' => $tanjungPriok->id, 'status' => 'Cargo Received', 'event_time' => Carbon::parse('2026-11-09 14:00:00'), 'description' => 'Cargo received and inspected at Tanjung Priok warehouse.', 'created_by' => $operator?->id],
            ['location_id' => $tanjungPriok->id, 'status' => 'Departed Port', 'event_time' => Carbon::parse('2026-11-10 09:30:00'), 'description' => 'Vessel departed Tanjung Priok en route to Singapore.', 'created_by' => $operator?->id],
            ['location_id' => $singapore->id, 'status' => 'Arrived at Destination Port', 'event_time' => Carbon::parse('2026-11-14 12:00:00'), 'description' => 'Vessel berthed at PSA Singapore Port.', 'created_by' => $operator?->id],
            ['location_id' => $singapore->id, 'status' => 'Delivered', 'event_time' => Carbon::parse('2026-11-14 17:15:00'), 'description' => 'Cargo successfully cleared customs and received by consignee.', 'created_by' => $operator?->id],
        ]);

        // Shipment 2: Delayed (Sea freight Surabaya -> Jakarta)
        $s2 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-002'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $samudera->id,
                'origin_id' => $tanjungPerak->id,
                'destination_id' => $tanjungPriok->id,
                'cargo_type' => 'Textile & Manufactured Garments',
                'weight' => 12400.00,
                'volume' => 38.00,
                'expected_departure' => Carbon::parse('2026-11-15 06:00:00'),
                'expected_arrival' => Carbon::parse('2026-11-18 12:00:00'),
                'estimated_arrival' => Carbon::parse('2026-11-19 18:00:00'),
                'actual_departure' => Carbon::parse('2026-11-15 08:00:00'),
                'current_status' => 'delayed',
                'is_delayed' => true,
                'delay_hours' => 30,
                'notes' => 'Delayed due to port congestion and adverse weather in Java Sea.',
            ]
        );
        $s2->items()->createMany([
            ['item_name' => 'Cotton Yarns & Woven Rolls', 'item_code' => 'TXT-YRN-01', 'quantity' => 80, 'weight' => 6400.00, 'volume' => 20.00, 'declared_value' => 24000.00],
            ['item_name' => 'Export Finished Garments (Denim)', 'item_code' => 'GRM-DNM-45', 'quantity' => 120, 'weight' => 6000.00, 'volume' => 18.00, 'declared_value' => 55000.00],
        ]);
        $s2->trackingEvents()->createMany([
            ['location_id' => $tanjungPerak->id, 'status' => 'Loaded on Vessel', 'event_time' => Carbon::parse('2026-11-15 05:00:00'), 'description' => 'Container loaded onto MV Samudera Mas.', 'created_by' => $operator?->id],
            ['location_id' => $tanjungPerak->id, 'status' => 'Departed Port', 'event_time' => Carbon::parse('2026-11-15 08:00:00'), 'description' => 'Vessel departed Tanjung Perak, Surabaya.', 'created_by' => $operator?->id],
            ['location_id' => $tanjungPriok->id, 'status' => 'Delayed in Transit', 'event_time' => Carbon::parse('2026-11-17 10:00:00'), 'description' => 'Berthing slot re-allocated due to Tanjung Priok harbor congestion.', 'created_by' => $operator?->id],
        ]);
        $s2->exceptions()->create([
            'exception_type' => 'Port Congestion',
            'severity' => 'medium',
            'description' => 'Berth queue delay at Tanjung Priok Terminal 2 due to bad weather and high vessel backlog.',
            'occurred_at' => Carbon::parse('2026-11-17 09:30:00'),
            'is_resolved' => false,
        ]);

        // Shipment 3: Delivered (Air Freight Jakarta -> Singapore)
        $s3 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-003'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $dhl->id,
                'origin_id' => $cgkAirport->id,
                'destination_id' => $singapore->id,
                'cargo_type' => 'Pharmaceutical & Cold Chain Vaccines',
                'weight' => 850.00,
                'volume' => 4.50,
                'expected_departure' => Carbon::parse('2026-11-16 10:00:00'),
                'expected_arrival' => Carbon::parse('2026-11-16 14:00:00'),
                'estimated_arrival' => Carbon::parse('2026-11-16 14:00:00'),
                'actual_departure' => Carbon::parse('2026-11-16 10:15:00'),
                'actual_arrival' => Carbon::parse('2026-11-16 13:45:00'),
                'current_status' => 'delivered',
                'is_delayed' => false,
                'delay_hours' => 0,
                'notes' => 'Strict 2C - 8C thermal monitoring active.',
            ]
        );
        $s3->items()->createMany([
            ['item_name' => 'Temperature-Controlled Vaccines (2-8C)', 'item_code' => 'VAX-COVID-02', 'quantity' => 20, 'weight' => 450.00, 'volume' => 2.50, 'declared_value' => 120000.00],
            ['item_name' => 'Insulin Glargine Disposable Pens', 'item_code' => 'MED-INS-09', 'quantity' => 40, 'weight' => 400.00, 'volume' => 2.00, 'declared_value' => 75000.00],
        ]);
        $s3->trackingEvents()->createMany([
            ['location_id' => $cgkAirport->id, 'status' => 'Departed Airport', 'event_time' => Carbon::parse('2026-11-16 10:15:00'), 'description' => 'Flight DHL Aviation departed CGK.', 'created_by' => $operator?->id],
            ['location_id' => $singapore->id, 'status' => 'Delivered', 'event_time' => Carbon::parse('2026-11-16 13:45:00'), 'description' => 'Direct hand-over to Singapore Changi Medical Cargo Hub.', 'created_by' => $operator?->id],
        ]);

        // Shipment 4: In Transit (International Sea Freight Jakarta -> Rotterdam)
        $s4 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-004'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $maersk->id,
                'origin_id' => $tanjungPriok->id,
                'destination_id' => $rotterdam->id,
                'cargo_type' => 'Specialty Arabica Coffee Beans',
                'weight' => 19800.00,
                'volume' => 42.50,
                'expected_departure' => Carbon::parse('2026-11-12 14:00:00'),
                'expected_arrival' => Carbon::parse('2026-12-05 08:00:00'),
                'estimated_arrival' => Carbon::parse('2026-12-05 08:00:00'),
                'actual_departure' => Carbon::parse('2026-11-12 16:30:00'),
                'current_status' => 'in_transit',
                'is_delayed' => false,
                'delay_hours' => 0,
                'notes' => 'Export consignment in 40ft ventilated dry container.',
            ]
        );
        $s4->items()->createMany([
            ['item_name' => 'Gayo Arabica Green Beans (Grade 1)', 'item_code' => 'COF-GYO-01', 'quantity' => 300, 'weight' => 10000.00, 'volume' => 21.50, 'declared_value' => 65000.00],
            ['item_name' => 'Toraja Arabica Beans (Specialty)', 'item_code' => 'COF-TRJ-02', 'quantity' => 250, 'weight' => 9800.00, 'volume' => 21.00, 'declared_value' => 68000.00],
        ]);
        $s4->trackingEvents()->createMany([
            ['location_id' => $tanjungPriok->id, 'status' => 'Customs Cleared', 'event_time' => Carbon::parse('2026-11-11 11:00:00'), 'description' => 'Export customs PEB approved.', 'created_by' => $operator?->id],
            ['location_id' => $tanjungPriok->id, 'status' => 'Departed Port', 'event_time' => Carbon::parse('2026-11-12 16:30:00'), 'description' => 'Departed on MV Maersk Mc-Kinney Moller.', 'created_by' => $operator?->id],
            ['location_id' => $singapore->id, 'status' => 'Transshipment Hub Arrival', 'event_time' => Carbon::parse('2026-11-15 14:00:00'), 'description' => 'Transferred for deep-sea westbound voyage.', 'created_by' => $operator?->id],
        ]);

        // Shipment 5: In Transit (Medan -> Johor / Tanjung Pelepas)
        $s5 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-005'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $samudera->id,
                'origin_id' => $belawan->id,
                'destination_id' => $tanjungPelepas->id,
                'cargo_type' => 'Refined Palm Oil & Oleochemicals',
                'weight' => 15600.00,
                'volume' => 32.00,
                'expected_departure' => Carbon::parse('2026-11-17 08:00:00'),
                'expected_arrival' => Carbon::parse('2026-11-19 16:00:00'),
                'estimated_arrival' => Carbon::parse('2026-11-19 16:00:00'),
                'actual_departure' => Carbon::parse('2026-11-17 08:45:00'),
                'current_status' => 'in_transit',
                'is_delayed' => false,
                'delay_hours' => 0,
                'notes' => 'Food grade ISO tank containers.',
            ]
        );
        $s5->items()->createMany([
            ['item_name' => 'RBD Palm Stearin (Food Grade)', 'item_code' => 'OLEO-STR-10', 'quantity' => 200, 'weight' => 8000.00, 'volume' => 16.50, 'declared_value' => 18500.00],
            ['item_name' => 'Glycerin 99.7% USP Grade', 'item_code' => 'OLEO-GLY-20', 'quantity' => 180, 'weight' => 7600.00, 'volume' => 15.50, 'declared_value' => 21000.00],
        ]);
        $s5->trackingEvents()->createMany([
            ['location_id' => $belawan->id, 'status' => 'Loaded on Vessel', 'event_time' => Carbon::parse('2026-11-17 06:30:00'), 'description' => 'Tank containers loaded at Belawan Terminal.', 'created_by' => $operator?->id],
            ['location_id' => $belawan->id, 'status' => 'Departed Port', 'event_time' => Carbon::parse('2026-11-17 08:45:00'), 'description' => 'Sailing across Malacca Strait to Tanjung Pelepas.', 'created_by' => $operator?->id],
        ]);

        // Shipment 6: Pending (Air Freight Jakarta -> Singapore)
        $s6 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-006'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $fedex->id,
                'origin_id' => $cgkAirport->id,
                'destination_id' => $singapore->id,
                'cargo_type' => 'Automotive Precision Spare Parts',
                'weight' => 1420.00,
                'volume' => 6.80,
                'expected_departure' => Carbon::parse('2026-11-18 09:00:00'),
                'expected_arrival' => Carbon::parse('2026-11-18 15:00:00'),
                'estimated_arrival' => Carbon::parse('2026-11-18 15:00:00'),
                'current_status' => 'pending',
                'is_delayed' => false,
                'delay_hours' => 0,
                'notes' => 'Awaiting customs document release at CGK air cargo zone.',
            ]
        );
        $s6->items()->createMany([
            ['item_name' => 'Brembo High Performance Brake Calipers', 'item_code' => 'AUTO-BRK-99', 'quantity' => 50, 'weight' => 620.00, 'volume' => 3.00, 'declared_value' => 34000.00],
            ['item_name' => 'Turbocharger Assembly Units', 'item_code' => 'AUTO-TRB-44', 'quantity' => 35, 'weight' => 800.00, 'volume' => 3.80, 'declared_value' => 48000.00],
        ]);

        // Shipment 7: Delayed (Import Singapore -> Jakarta)
        $s7 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-007'],
            [
                'customer_id' => $customer->id,
                'carrier_id' => $maersk->id,
                'origin_id' => $singapore->id,
                'destination_id' => $tanjungPriok->id,
                'cargo_type' => 'Solar Panels & Photovoltaic Inverters',
                'weight' => 16500.00,
                'volume' => 40.00,
                'expected_departure' => Carbon::parse('2026-11-14 07:00:00'),
                'expected_arrival' => Carbon::parse('2026-11-17 18:00:00'),
                'estimated_arrival' => Carbon::parse('2026-11-19 06:00:00'),
                'actual_departure' => Carbon::parse('2026-11-14 09:00:00'),
                'current_status' => 'delayed',
                'is_delayed' => true,
                'delay_hours' => 36,
                'notes' => 'Customs hold at Tanjung Priok for technical inspection.',
            ]
        );
        $s7->items()->createMany([
            ['item_name' => 'Monocrystalline Solar Panels 550W', 'item_code' => 'SOLAR-550W', 'quantity' => 300, 'weight' => 10500.00, 'volume' => 26.00, 'declared_value' => 72000.00],
            ['item_name' => 'Hybrid Solar Inverters 10kW', 'item_code' => 'SOLAR-INV-10', 'quantity' => 60, 'weight' => 6000.00, 'volume' => 14.00, 'declared_value' => 54000.00],
        ]);
        $s7->trackingEvents()->createMany([
            ['location_id' => $singapore->id, 'status' => 'Departed Port', 'event_time' => Carbon::parse('2026-11-14 09:00:00'), 'description' => 'Vessel departed PSA Singapore.', 'created_by' => $operator?->id],
            ['location_id' => $tanjungPriok->id, 'status' => 'Arrived at Destination Port', 'event_time' => Carbon::parse('2026-11-17 08:00:00'), 'description' => 'Vessel docked at Jakarta Tanjung Priok.', 'created_by' => $operator?->id],
            ['location_id' => $tanjungPriok->id, 'status' => 'Customs Hold', 'event_time' => Carbon::parse('2026-11-17 11:30:00'), 'description' => 'Red line notification: physical verification required.', 'created_by' => $operator?->id],
        ]);
        $s7->exceptions()->create([
            'exception_type' => 'Customs Hold',
            'severity' => 'high',
            'description' => 'Customs authority requested physical inspection and SNI certificate matching.',
            'occurred_at' => Carbon::parse('2026-11-17 11:30:00'),
            'is_resolved' => false,
        ]);

        // Demo Notifications
        Notification::create([
            'user_id' => $customer->id,
            'shipment_id' => $s2->id,
            'title' => 'Shipment Delay Alert: CT-2026-002',
            'message' => 'Your shipment CT-2026-002 has encountered a delay of approx 30 hours due to port congestion at Tanjung Priok.',
            'type' => 'warning',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $customer->id,
            'shipment_id' => $s7->id,
            'title' => 'Customs Hold Alert: CT-2026-007',
            'message' => 'Shipment CT-2026-007 is under customs physical inspection at Tanjung Priok.',
            'type' => 'alert',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $customer->id,
            'shipment_id' => $s1->id,
            'title' => 'Delivery Completed: CT-2026-001',
            'message' => 'Shipment CT-2026-001 has been safely delivered to consignee in Singapore.',
            'type' => 'success',
            'is_read' => true,
            'read_at' => Carbon::parse('2026-11-14 18:00:00'),
        ]);

        // Demo Audit Logs
        AuditLog::create([
            'user_id' => $operator?->id,
            'action' => 'UPDATE_STATUS',
            'auditable_type' => Shipment::class,
            'auditable_id' => $s2->id,
            'old_values' => ['current_status' => 'in_transit', 'is_delayed' => false],
            'new_values' => ['current_status' => 'delayed', 'is_delayed' => true, 'delay_hours' => 30],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        AuditLog::create([
            'user_id' => $operator?->id,
            'action' => 'RECORD_EXCEPTION',
            'auditable_type' => Shipment::class,
            'auditable_id' => $s7->id,
            'new_values' => ['exception_type' => 'Customs Hold', 'severity' => 'high'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
    }
}
