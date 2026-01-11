<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trajet extends Model
{
    protected $fillable = [
        'itineraire_id',
        'cout_moyen',
        'type_vehicule'
    ];
    public function itineraire(){
        return $this->belongsTo(Itineraire::class);
    }

    public function avis(){
        return $this->hasMany(Avis::class);
    }
}
