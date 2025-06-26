<?php
/**
 * Dummy implementation of UserPasswordHasherInterface for testing purposes.
 *
 * @license MIT
 */

namespace App\Tests\DataFixtures;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class DummyPasswordHasher.
 *
 * Provides a dummy password hasher for tests.
 */
class DummyPasswordHasher implements UserPasswordHasherInterface
{
    /**
     * Hash the plain password.
     *
     * @param mixed  $user          the user entity
     * @param string $plainPassword the plain password to hash
     *
     * @return string the hashed password
     */
    public function hashPassword(mixed $user, string $plainPassword): string
    {
        return 'hashed_password';
    }

    /**
     * Verify if the given plain password is valid for the user.
     *
     * @param mixed  $user          the user entity
     * @param string $plainPassword the plain password to verify
     *
     * @return bool true if password is valid, false otherwise
     */
    public function isPasswordValid(mixed $user, string $plainPassword): bool
    {
        return true;
    }

    /**
     * Check if the password needs to be rehashed.
     *
     * @param mixed $user the user entity
     *
     * @return bool true if password needs rehash, false otherwise
     */
    public function needsRehash(mixed $user): bool
    {
        return false;
    }
}
