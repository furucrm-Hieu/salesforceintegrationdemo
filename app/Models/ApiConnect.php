<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ApiConnect extends Model
{
    protected $table = 'api_connect';

    protected $primaryKey = 'id';

    protected $fillable = [
        'accessToken', 'refreshToken', 'expried'
    ];
}
