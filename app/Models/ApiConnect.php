<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ApiConnect extends Model
{
    use Notifiable;

    protected $table = 'api_connect';

    protected $primaryKey = 'id';

    protected $fillable = [
        'accessToken', 'refreshToken', 'status'
    ];
}
