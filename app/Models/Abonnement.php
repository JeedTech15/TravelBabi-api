<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Abonnement extends Model
{
    protected $fillable = ['libelle','description','prix','duree_validite','populaire'];

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
    

    public function utilisateurs(){
        return $this->belongsToMany(User::class,'souscriptions');
    }
}
