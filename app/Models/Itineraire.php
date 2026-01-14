<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Itineraire extends Model
{
    protected $fillable = [
        'cout',
        'distance',
        'duree'
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
    public function trajets(){
        return $this->hasMany(Trajet::class);
    }

    public function noeuds(){
        return $this->belongsToMany(Noeud::class,'composer');
    }
}
