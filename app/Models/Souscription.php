<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Souscription extends Model
{
    protected $fillable = ['utilisateur_id','abonnement_id'];

    public function utilisateur(){
        return $this->belongsTo(User::class);
    }

    public function abonnement(){
        return $this->belongsTo(Abonnement::class);
    }
}
