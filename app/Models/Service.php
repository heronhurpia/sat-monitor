<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    
    public function services()
    {
        return $this->belongsTo(Transponder::class);
    }

    public function audios()
    {
        return $this->hasMany(Audio::class);
    }       
}
