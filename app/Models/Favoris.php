<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favoris extends Model
{
    protected $fillable = ['utilisateur_id'];

    public function utilisateur(){
        return $this->belongsTo(Utilisateur::class);
    }
}
