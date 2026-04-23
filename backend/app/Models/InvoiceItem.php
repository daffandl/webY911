<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'service_item_id',
        'name',
        'type',
        'description',
        'qty',
        'unit',
        'unit_price',
        'subtotal',
        'sort_order',
    ];

    protected $casts = [
        'qty'        => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Boot — auto-calculate subtotal before saving.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (InvoiceItem $item) {
            $item->subtotal = round($item->qty * $item->unit_price, 2);
        });
    }

    /**
     * Belongs to Invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Belongs to ServiceItem (nullable — custom items have no catalog entry)
     */
    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
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
}
