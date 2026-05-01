<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['montant', 'dateP', 'methode', 'status', 'reservation_id'];
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
