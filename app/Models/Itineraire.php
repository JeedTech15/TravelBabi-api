<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itineraire extends Model
{
    protected $fillable = [
        'cout',
        'distance',
        'duree'
    ];
    public function trajets(){
        return $this->hasMany(Trajet::class);
    }

    public function noeuds(){
        return $this->belongsToMany(Noeud::class,'composer');
    }
}
