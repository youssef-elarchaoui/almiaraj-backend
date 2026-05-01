<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    protected $fillable = [
        'service_id',
        'client_id',
        'nbPers',
        'prixUnitaire',
        'prixTotal',
        'status',
        'payment_status',
        'check_in',
        'check_out',
        'type_chambre',
        'date_depart',
        'date_retour',
        'voucher_generated',
        'reference'
    ];


    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'date_depart' => 'date',
        'date_retour' => 'date',
        'voucher_generated' => 'boolean',
        'nbPers' => 'integer',
    ];

    // ✅ Constantes pour les statuts
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_REFUNDED = 'refunded';

    // ✅ Relations
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class);
    }

    public function passagers(): HasMany
    {
        return $this->hasMany(Passager::class);
    }

    public function voucher(): HasOne
    {
        return $this->hasOne(Voucher::class);
    }

    // ✅ توليد رقم مرجعي فريد
    public static function generateReference(): string
    {
        do {
            $reference = 'RES-' . strtoupper(uniqid());
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    // ✅ حساب السعر الكلي تلقائياً
    public static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            $reservation->prixTotal = $reservation->nbPers * $reservation->prixUnitaire;
            if (empty($reservation->reference)) {
                $reservation->reference = self::generateReference();
            }
        });

        static::updating(function ($reservation) {
            $reservation->prixTotal = $reservation->nbPers * $reservation->prixUnitaire;
        });
    }

    // ✅ Helper methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() && !$this->voucher_generated;
    }

    // ✅ Scope pour les filtres
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
