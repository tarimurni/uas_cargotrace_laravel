<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'exception_type',
        'severity',
        'description',
        'occurred_at',
        'is_resolved',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
