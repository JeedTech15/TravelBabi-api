<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PubUtilisateur extends Model
{
    protected $table = 'pub_utilisateur';

    protected $fillable = ['pub_id','utilisateur_id'];

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

    public function pub(){
        return $this->belongsTo(Pub::class);
    }
}
