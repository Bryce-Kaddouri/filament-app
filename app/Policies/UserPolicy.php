<?php

namespace App\Policies;

use App\Enums\RoleUserEnum;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value;

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value && $model->id != $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value && $model->id != $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return RoleUserEnum::ROLE_ADMIN->value == $user->role->value && $model->id != $user->id;
    }
}
