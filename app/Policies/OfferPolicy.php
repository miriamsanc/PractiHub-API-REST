<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OfferPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Offer $offer): bool
    {
        // las empresas pueden ver cualquier oferta
        if ($user->role === 'company') {
            return true;
        }

        // Si es estudiante, solo puede verla si está activa
        return $offer->is_active === true;
    }

    /**
     * Determine whether the user can create models.
     * Solo los usuarios con rol 'empresa' pueden crear ofertas.
     */
    public function create(User $user): bool
    {
        return $user->role === 'company';
    }

    /**
     * Determine whether the user can update the model.
     * Solo la empresa creadora de la oferta puede editarla.
     */
    public function update(User $user, Offer $offer): bool
    {
        return $user->role === 'company' && $user->id === $offer->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Solo la empresa creadora de la oferta puede eliminarla.
     */
    public function delete(User $user, Offer $offer): bool
    {
        return $user->role === 'company' && $user->id === $offer->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Offer $offer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Offer $offer): bool
    {
        return false;
    }
}
