<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmail(): void
    {
        $user = new User();
        $email = 'user@example.com';

        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($email, $user->getUserIdentifier());
    }

    public function testUsernameAndNickname(): void
    {
        $user = new User();
        $nickname = 'TestNick';

        $user->setUsername($nickname);
        $this->assertSame($nickname, $user->getUsername());
    }

    public function testRoles(): void
    {
        $user = new User();

        // test domyślna rola dodana
        $this->assertContains(UserRole::ROLE_USER->value, $user->getRoles());

        // test przypisanych ról
        $user->setRoles([UserRole::ROLE_ADMIN->value, 'ROLE_CUSTOM']);
        $roles = $user->getRoles();

        $this->assertContains(UserRole::ROLE_ADMIN->value, $roles);
        $this->assertContains(UserRole::ROLE_USER->value, $roles); // powinien być automatycznie dodany
        $this->assertContains('ROLE_CUSTOM', $roles);
        $this->assertCount(3, array_unique($roles));
    }

    public function testPassword(): void
    {
        $user = new User();
        $password = 'hashed_password';

        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        // Jeśli masz jakieś dane tymczasowe, powinieneś je wyczyścić tutaj
        $this->expectNotToPerformAssertions();
        $user->eraseCredentials();
    }
}
