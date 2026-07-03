<?php

// app/Models/Sparepart.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sparepart extends Model
{
    protected $fillable = ['category_id', 'part_name', 'price', 'stock'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}