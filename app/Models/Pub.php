<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pub extends Model
{
    protected $fillable = ['video_url','duree_video','nbr_etoile'];

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
        return $this->belongsToMany(User::class,'pub_utilisateur');
    }
}
