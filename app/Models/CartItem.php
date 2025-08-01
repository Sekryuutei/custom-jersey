<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'file_name',
        'size',
        'quantity',
        'price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}