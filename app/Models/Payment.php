<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'payment_method',
        'amount',
        'status',
        'snap_token'
    ];
    

    public function setStatusPending()
    {
        $this->attributes['status'] = 'pending';
        $this->save();
    }

    /**
     * Set status to Success
     *
     * @return void
     */
    public function setStatusSuccess()
    {
        $this->attributes['status'] = 'success';
        $this->save();
    }

    /**
     * Set status to Failed
     *
     * @return void
     */
    public function setStatusFailed()
    {
        $this->attributes['status'] = 'failed';
        $this->save();
    }

    /**
     * Set status to Expired
     *
     * @return void
     */
    public function setStatusExpired()
    {
        $this->attributes['status'] = 'expired';
        $this->save();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
