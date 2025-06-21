<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class AppFixturesTest extends TestCase
{
    public function testLoadCallsFlush()
    {
        $manager = $this->createMock(ObjectManager::class);

        // Oczekujemy, że flush zostanie wywołane dokładnie raz
        $manager->expects($this->once())
            ->method('flush');

        $fixtures = new AppFixtures();
        $fixtures->load($manager);
    }
}
