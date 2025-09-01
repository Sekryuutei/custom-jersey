<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'payment_id',
        'template_id',
        'file_name',
        'size',
        'quantity',
        'price',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Mendefinisikan relasi bahwa setiap item pesanan milik satu template.
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
