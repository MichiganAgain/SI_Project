<?php
/**
 * User Service Interface.
 */


namespace App\Service;

use App\Entity\User;

/**
 * User Service Interface.
 */
interface UserServiceInterface
{
    /**
     * Save User.
     *
     * @param User $user
     *
     * @return void
     */
    public function save(User $user): void;

    /**
     * Can User be deleted?
     *
     * @param User $user User entity
     *
     * @return bool Result
     */
    public function canBeDeleted(User $user): bool;

    /**
     * Delete User.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool;
}
