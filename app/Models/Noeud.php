<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noeud extends Model
{
    protected $fillable = ['longitude','latitude'];

    public function itineraires(){
        return $this->belongsToMany(Itineraire::class,'composer');
    }
}
