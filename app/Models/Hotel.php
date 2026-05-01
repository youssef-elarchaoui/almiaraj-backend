<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'destination_id',
        'amenities',
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
