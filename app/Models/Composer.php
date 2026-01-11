<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Composer extends Model
{
    protected $fillable = ['itineraire_id','noeud_id'];

    public function itineraire(){
        return $this->belongsTo(Itineraire::class);
    }

    public function noeud(){
        return $this->belongsTo(Noeud::class);
    }
}
