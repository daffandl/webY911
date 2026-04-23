<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'price',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to set default type
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->type)) {
                $model->type = 'jasa';
            }
        });
    }

    /**
     * Scope: only active items
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereRaw('is_active IS TRUE');
    }

    /**
     * Scope: filter by type jasa
     */
    public function scopeJasa(Builder $query): Builder
    {
        return $query->where('type', 'jasa');
    }

    /**
     * Scope: filter by type sparepart
     */
    public function scopeSparepart(Builder $query): Builder
    {
        return $query->where('type', 'sparepart');
    }

    /**
     * Scope: filter by type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Human-readable type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sparepart' => 'Sparepart',
            'jasa'      => 'Jasa',
            default     => ucfirst($this->type),
        };
    }

    /**
     * Formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Check if item is jasa
     */
    public function isJasa(): bool
    {
        return $this->type === 'jasa';
    }

    /**
     * Check if item is sparepart
     */
    public function isSparepart(): bool
    {
        return $this->type === 'sparepart';
    }

    /**
     * Invoice items that use this service item
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
