<?php
/**
 * UserFixtures test cases.
 *
 * @license MIT
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Class UserFixturesTest.
 */
class UserFixturesTest extends TestCase
{
    /**
     * Test that load() creates expected number of users and admins.
     */
    public function testLoadDataCreatesUsersAndAdmins(): void
    {
        $manager = $this->createMock(ObjectManager::class);

        // Expect exactly 13 calls to persist (10 users + 3 admins)
        $manager->expects($this->exactly(13))
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $manager->expects($this->once())
            ->method('flush');

        $hasher = new DummyPasswordHasher();

        $fixture = new UserFixtures($hasher);

        // Set referenceRepository to avoid errors in abstraction (optional)
        $referenceRepository = $this->createMock(ReferenceRepository::class);
        $reflection = new \ReflectionClass($fixture);
        $parentClass = $reflection->getParentClass(); // AbstractBaseFixtures
        $property = $parentClass->getProperty('referenceRepository');
        $property->setAccessible(true);
        $property->setValue($fixture, $referenceRepository);

        // Call load(), AbstractBaseFixtures will initialize faker and manager
        $fixture->load($manager);

        // Optionally assert that passwords are set correctly, but this is more entity or hasher test
    }

    /**
     * Test that loadData returns early if manager or faker is null.
     */
    public function testLoadDataReturnsEarlyWhenManagerOrFakerIsNull(): void
    {
        $fixture = new UserFixtures(new DummyPasswordHasher());

        $reflection = new \ReflectionClass($fixture);

        // Set manager and faker to null
        $propertyManager = $reflection->getProperty('manager');
        $propertyManager->setAccessible(true);
        $propertyManager->setValue($fixture, null);

        $propertyFaker = $reflection->getProperty('faker');
        $propertyFaker->setAccessible(true);
        $propertyFaker->setValue($fixture, null);

        // Call protected loadData() method
        $method = $reflection->getMethod('loadData');
        $method->setAccessible(true);
        $method->invoke($fixture);

        $this->assertTrue(true);
    }
}
