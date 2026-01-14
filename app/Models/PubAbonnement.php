<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PubAbonnement extends Model
{
    protected $table = 'pub_abonnement';

    protected $fillable = [
        'pub_id',
        'abonnement_id'
    ];

    public $timestamps = false;

    public function pub(){
        return $this->belongsTo(Pub::class);
    }

    public function abonnement(){
        return $this->belongsTo(Abonnement::class);
    }
}
