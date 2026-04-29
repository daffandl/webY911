<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type',
        'name',
        'description',
        'base_price',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get price by service type
     */
    public static function getPriceByType(string $serviceType): ?decimal
    {
        $price = static::where('service_type', $serviceType)
            ->where('is_active', true)
            ->first();

        return $price?->base_price;
    }

    /**
     * Get all active service prices
     */
    public static function getAllActive(): array
    {
        return static::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($item) => [
                'service_type' => $item->service_type,
                'name' => $item->name,
                'price' => $item->base_price,
            ])
            ->toArray();
    }

    /**
     * Scope for active prices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
