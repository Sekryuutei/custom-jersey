<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image_path'];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Accessor untuk menghitung rata-rata rating.
     * Dapat diakses sebagai $template->average_rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating');
    }
}
