<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = ['libelle','description','prix','duree_validite','populaire'];

    public function utilisateurs(){
        return $this->belongsToMany(User::class,'souscriptions');
    }
}
