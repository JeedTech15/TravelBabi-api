<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['nom','numero','image','nbr_etoile','otp','expires_otp','is_verified','device_token'];

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
