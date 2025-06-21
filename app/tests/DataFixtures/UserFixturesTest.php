<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class UserFixturesTest extends TestCase
{
    public function testLoadDataCreatesUsersAndAdmins()
    {
        $manager = $this->createMock(ObjectManager::class);

        // Spodziewamy się 13 wywołań persist (10 userów + 3 adminów)
        $manager->expects($this->exactly(13))
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $manager->expects($this->once())
            ->method('flush');

        $hasher = new DummyPasswordHasher();

        $fixture = new UserFixtures($hasher);

        // Podstawiamy referenceRepository, choć tutaj może nie być konieczne,
        // ale żeby uniknąć błędów w abstrakcji:
        $referenceRepository = $this->createMock(ReferenceRepository::class);
        $reflection = new \ReflectionClass($fixture);
        $parentClass = $reflection->getParentClass(); // AbstractBaseFixtures
        $property = $parentClass->getProperty('referenceRepository');
        $property->setAccessible(true);
        $property->setValue($fixture, $referenceRepository);

        // Wywołujemy load() — w AbstractBaseFixtures ładuje faker i manager, więc zadziała
        $fixture->load($manager);

        // Dodatkowo możesz zweryfikować, że użytkownicy mają prawidłowo ustawione hasło
        // ale to bardziej test entity lub hashera
    }
}


use Symfony\Component\PasswordHasher\PasswordAuthenticatedUserInterface;

class DummyPasswordHasher implements UserPasswordHasherInterface
{
    // Zmieniamy typ parametru na mixed, żeby ominąć problem z typem
    public function hashPassword(mixed $user, string $plainPassword): string
    {
        return 'hashed_password';
    }

    public function isPasswordValid(mixed $user, string $plainPassword): bool
    {
        return true;
    }

    public function needsRehash(mixed $user): bool
    {
        return false;
    }
}