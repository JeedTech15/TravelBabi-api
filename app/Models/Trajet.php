<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Trajet extends Model
{
    protected $fillable = [
        'itineraire_id',
        'cout_moyen',
        'type_vehicule'
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
    public function itineraire(){
        return $this->belongsTo(Itineraire::class);
    }

    public function avis(){
        return $this->hasMany(Avis::class);
    }
}
