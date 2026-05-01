<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passager extends Model
{
    protected $table = 'passagers';

    protected $fillable = [
        'reservation_id',
        'nomPas',
        'prenomPas',
        'cinPas',
        'passportPas',
        'type_passager'
    ];


    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function getFullNameAttribute()
    {
        return $this->prenomPas . ' ' . $this->nomPas;
    }

    public function hasValidId()
    {
        return !empty($this->cinPas) || !empty($this->passportPas);
    }
}
