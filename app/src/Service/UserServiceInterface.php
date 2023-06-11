<?php

namespace App\Service;

use App\Entity\User;

interface UserServiceInterface
{
    public function save(User $user): void;

    /**
     * Can User be deleted?
     *
     * @param User $user User entity
     *
     * @return bool Result
     */
    public function canBeDeleted(User $user): bool;
    public function delete(User $user): bool;
}