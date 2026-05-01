<?php
// app/Models/Billet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billet extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';
    
    protected $fillable = [
        'id',
        'typeBi',
        'villeDepartBi',
        'villeArriveeBi',
        'destination_id',
        'dateDepartBi',
        'dateRetourBi',
    ];
    
    public function service()
    {
        return $this->belongsTo(Service::class, 'id', 'id');
    }
    
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }
}