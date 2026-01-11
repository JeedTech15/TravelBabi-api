<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'utilisateur_id',
        'type',
        'status_paiement',
        'montant'
    ];
    public function utilisateur(){
        return $this->belongsTo(User::class);
    }
}
