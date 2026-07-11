<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'paymentable_id',
        'paymentable_type',
        'amount', 
        'payment_status',
        'payment_method',
    ];

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }
}