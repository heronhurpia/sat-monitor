<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transponder extends Model
{
    use HasFactory;

    public function services()
    {
        return $this->hasMany(Service::class)->with('audios');
    }

    public function audios()
    {
        return $this->hasManyThrough(Audio::class, Service::class);
    }    
}
