<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PubUtilisateur extends Model
{
    protected $table = 'pub_utilisateur';

    protected $fillable = ['pub_id','utilisateur_id'];

    public function utilisateur(){
        return $this->belongsTo(Utilisateur::class);
    }

    public function pub(){
        return $this->belongsTo(Pub::class);
    }
}
