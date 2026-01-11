<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    protected $fillable = ['utilisateur_id','pack_id'];

    public function utilisateur(){
        return $this->belongsTo(User::class);
    }

    public function pack(){
        return $this->belongsTo(Pack::class);
    }
}
