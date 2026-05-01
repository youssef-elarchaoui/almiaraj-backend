<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'nomServ',
        'description',
        'prix',
        'type',
        'image',
        'rating' 
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    public function hotel()
    {
        return $this->hasOne(Hotel::class, 'id', 'id');
    }
    public function voyage()
    {
        return $this->hasOne(Voyage::class, 'id', 'id');
    }
    public function hajjOmra()
    {
        return $this->hasOne(HajjOmra::class, 'id', 'id');
    }
    public function billet()
    {
        return $this->hasOne(Billet::class, 'id', 'id');
    }
    public function avis()
    {
        return $this->hasMany(Avis::class);
    }
}
