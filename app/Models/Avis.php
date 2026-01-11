<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    protected $fillable = [
        'trajet_id',
        'utilisateur_id',
        'note',
        'commentaire'
    ];
    public function utilisateur(){
        return $this->belongsTo(User::class);
    }

    public function trajet(){
        return $this->belongsTo(Trajet::class);
    }
}
