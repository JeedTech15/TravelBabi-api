<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaiementAbonnement extends Model
{
    protected $fillable = [
        'data',
        'prix',
        'statut',
        'id_user',
        'id_abonnement',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public $incrementing = false; // empêche l'auto-incrémentation
    protected $keyType = 'string'; // la clé primaire sera une string

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function utilisateur(){
        return $this->belongsTo(User::class);
    }

    public function pack(){
        return $this->belongsTo(Pack::class);
    }
}
