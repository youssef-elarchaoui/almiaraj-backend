<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HajjOmra extends Model
{
    protected $table = 'hajj_omras';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = [
        'id',
        'type',
        'formule',
        'dateDepartHO',
        'dateRetourHO',
        'duree',
        'typeChambre'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'id', 'id');
    }
}
