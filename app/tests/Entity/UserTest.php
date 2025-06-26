<?php
/**
 * UserTest test cases.
 *
 * @license MIT
 */

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest.
 *
 * Test cases for the User entity.
 */
class UserTest extends TestCase
{
    /**
     * Test setting and getting the email, and user identifier.
     */
    public function testEmail(): void
    {
        $user = new User();
        $email = 'user@example.com';

        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($email, $user->getUserIdentifier());
    }

    /**
     * Test setting and getting the username (nickname).
     */
    public function testUsernameAndNickname(): void
    {
        $user = new User();
        $nickname = 'TestNick';

        $user->setUsername($nickname);
        $this->assertSame($nickname, $user->getUsername());
    }

    /**
     * Test roles assignment and retrieval.
     *
     * Ensures the default ROLE_USER is always present,
     * and custom roles are handled correctly.
     */
    public function testRoles(): void
    {
        $user = new User();

        // Default role is always added
        $this->assertContains(UserRole::ROLE_USER->value, $user->getRoles());

        // Assign roles including a custom one
        $user->setRoles([UserRole::ROLE_ADMIN->value, 'ROLE_CUSTOM']);
        $roles = $user->getRoles();

        $this->assertContains(UserRole::ROLE_ADMIN->value, $roles);
        $this->assertContains(UserRole::ROLE_USER->value, $roles); // should be automatically added
        $this->assertContains('ROLE_CUSTOM', $roles);
        $this->assertCount(3, array_unique($roles));
    }

    /**
     * Test setting and getting the password.
     */
    public function testPassword(): void
    {
        $user = new User();
        $password = 'hashed_password';

        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    /**
     * Test eraseCredentials method.
     *
     * Ensure it does not perform any assertions (usually clears sensitive data).
     */
    public function testEraseCredentials(): void
    {
        $user = new User();
        $this->expectNotToPerformAssertions();
        $user->eraseCredentials();
    }
}
