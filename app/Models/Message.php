<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'nomM',
        'numTelM',
        'emailM',
        'contenu',
        'dateM',
        'statusM',
        'client_id',
        'type'
    ];

    protected $casts = [
        'dateM' => 'date'
    ];

    // Constantes pour le statut
    const STATUS_PENDING = 'en_attente';
    const STATUS_READ = 'lu';
    const STATUS_REPLIED = 'repondu';
    const STATUS_ARCHIVED = 'archive';

    // Relation avec le client
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Helper pour marquer comme lu
    public function markAsRead()
    {
        $this->update(['statusM' => self::STATUS_READ]);
    }

    // Helper pour marquer comme répondu
    public function markAsReplied()
    {
        $this->update(['statusM' => self::STATUS_REPLIED]);
    }
}
