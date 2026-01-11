<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['utilisateur_id','title','message','body'];

    public function utilisateur(){
        return $this->belongsTo(User::class);
    }
}
