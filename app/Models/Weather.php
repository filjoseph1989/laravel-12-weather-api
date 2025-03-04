<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    protected $fillable = [
        'city',
        'temperature',
        'description',
        'humidity',
        'wind_speed',
    ];
}