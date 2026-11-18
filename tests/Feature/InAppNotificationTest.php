<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use App\Services\ExceptionService;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InAppNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $operator;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::firstOrCreate(
            ['email' => 'customer_notif@cargotrace.com'],
            ['name' => 'Notif Customer', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->operator = User::firstOrCreate(
            ['email' => 'operator_notif@cargotrace.com'],
            ['name' => 'Notif Operator', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->carrier = Carrier::firstOrCreate(
            ['code' => 'CR_NOTIF_TEST'],
            ['name' => 'Notification Carrier', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'LOC_NTF_ORG'],
            ['name' => 'Origin Port', 'type' => 'seaport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'LOC_NTF_DST'],
            ['name' => 'Destination Port', 'type' => 'seaport', 'city' => 'Surabaya', 'country' => 'Indonesia', 'latitude' => -7.2, 'longitude' => 112.7]
        );

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-NTF-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Test Cargo',
            'weight' => 450,
            'volume' => 3.5,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(2),
            'estimated_arrival' => now()->addDays(2),
            'current_status' => 'CREATED',
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);
    }

    public function test_status_transition_triggers_customer_notification(): void
    {
        $shipmentService = app(ShipmentService::class);
        $shipmentService->transitionStatus($this->shipment, 'BOOKED', $this->operator);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'shipment_id' => $this->shipment->id,
            'type' => 'status_update',
            'is_read' => false,
        ]);
    }

    public function test_delay_triggers_customer_notification(): void
    {
        // Push estimated arrival beyond threshold
        $this->shipment->update([
            'estimated_arrival' => now()->addDays(4), // +48h delay
        ]);

        $etaService = app(EtaService::class);
        $etaService->evaluateAndApply($this->shipment, $this->operator);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'shipment_id' => $this->shipment->id,
            'type' => 'delay_alert',
        ]);
    }

    public function test_exception_triggers_customer_notification(): void
    {
        $exceptionService = app(ExceptionService::class);
        $exceptionService->createException($this->shipment, [
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'description' => 'Severe thunderstorm at transit strait',
            'status' => 'OPEN',
        ], $this->operator);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'shipment_id' => $this->shipment->id,
            'type' => 'exception_alert',
        ]);
    }

    public function test_user_can_view_notifications_page(): void
    {
        Notification::create([
            'user_id' => $this->customer->id,
            'shipment_id' => $this->shipment->id,
            'title' => 'Test Notification Title',
            'message' => 'Test Notification Message Content',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Notification Title');
        $response->assertSee('Test Notification Message Content');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $notif = Notification::create([
            'user_id' => $this->customer->id,
            'shipment_id' => $this->shipment->id,
            'title' => 'Unread Notification',
            'message' => 'Please read this',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('notifications.read', $notif));

        $response->assertRedirect();
        $this->assertTrue((bool) $notif->fresh()->is_read);
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Notif 1',
            'message' => 'Msg 1',
            'type' => 'info',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Notif 2',
            'message' => 'Msg 2',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertEquals(0, Notification::where('user_id', $this->customer->id)->where('is_read', false)->count());
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $otherUser = User::factory()->create();
        $notif = Notification::create([
            'user_id' => $otherUser->id,
            'title' => 'Secret Notif',
            'message' => 'Private',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('notifications.read', $notif));

        $response->assertStatus(403);
    }
}
