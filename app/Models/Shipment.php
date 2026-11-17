<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'cargo_type',
        'weight',
        'volume',
        'expected_departure',
        'expected_arrival',
        'estimated_arrival',
        'actual_departure',
        'actual_arrival',
        'current_status',
        'is_delayed',
        'delay_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'volume' => 'decimal:2',
            'expected_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'actual_departure' => 'datetime',
            'actual_arrival' => 'datetime',
            'is_delayed' => 'boolean',
            'delay_hours' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent(): HasOne
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
