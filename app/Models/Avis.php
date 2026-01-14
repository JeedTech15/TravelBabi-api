<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Avis extends Model
{
    protected $fillable = [
        'trajet_id',
        'utilisateur_id',
        'note',
        'commentaire'
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

    public function trajet(){
        return $this->belongsTo(Trajet::class);
    }
}
