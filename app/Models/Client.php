<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model

{
    public $incrementing = false;
    protected $keyType = 'int';
    use HasFactory;

    protected $fillable = [
        'id',
        'cin',
        'passport',
        'natCl',
        'dateInscription',
        'nomCl',
        'prenomCl',
        'numTelCl',
        'email',
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'id', 'id');
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }
    public function avis()
    {
        return $this->hasMany(Avis::class);
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
