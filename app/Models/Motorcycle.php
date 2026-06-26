<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motorcycle extends Model
{
    //
    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'plate_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
