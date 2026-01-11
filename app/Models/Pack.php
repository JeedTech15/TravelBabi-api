<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $fillable = [
        'nbr_etoile',
        'libelle',
        'prix',
        'populaire'
    ];
    public function utilisateurs(){
        return $this->belongsToMany(User::class,'achats');
    }
}
