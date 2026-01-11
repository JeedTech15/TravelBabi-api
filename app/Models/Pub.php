<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pub extends Model
{
    protected $fillable = ['video_url','duree_video','nbr_etoile'];

    public function utilisateurs(){
        return $this->belongsToMany(Utilisateur::class,'pub_utilisateur');
    }
}
