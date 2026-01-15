<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

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

    protected $fillable = ['nom','numero','image', 'email', 'nbr_etoile','otp','expires_otp_at','is_verified','device_token'];

    public function packs(){
        return $this->belongsToMany(Pack::class,'achats');
    }

    public function paiements(){
        return $this->hasMany(Paiement::class);
    }

    public function avis(){
        return $this->hasMany(Avis::class);
    }

    public function abonnement(){
        return $this->belongsToMany(Abonnement::class,'souscriptions');
    }

    public function notifications(){
        return $this->hasMany(Notification::class);
    }

    public function pubs(){
        return $this->belongsToMany(Pub::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
