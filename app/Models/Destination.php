<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $table = 'destinations';
    protected $fillable = ['pays', 'ville', 'continente', 'en_vedette', 'description', 'image'];

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }

    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }
}
