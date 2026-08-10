<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'offer_id', 'status', 'cv_path'];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
