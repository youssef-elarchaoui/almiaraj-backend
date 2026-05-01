<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = ['codeV', 'dateGeneration', 'urlPDF' ,'dateExpiration','reservation_id'];
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
