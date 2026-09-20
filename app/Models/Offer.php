<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    /** @use HasFactory<\Database\Factories\OfferFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'title', 'description', 'location', 'is_active'];

    public function company()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Scope: filtra por category_id exacto (si se proporciona).
     */
    public function scopeCategory($query, $categoryId)
    {
        return $query->when($categoryId, function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }
 
    /**
     * Scope: filtra por ubicación con coincidencia parcial (si se proporciona).
     */
    public function scopeLocation($query, $location)
    {
        return $query->when($location, function ($q) use ($location) {
            $q->where('location', 'like', '%' . $location . '%');
        });
    }
 
    /**
     * Scope: si el usuario no es empresa, solo muestra ofertas activas.
     */
    public function scopeVisibleTo($query, $user)
    {
        return $query->when($user->role !== 'company', function ($q) {
            $q->where('is_active', true);
        });
    }
}
