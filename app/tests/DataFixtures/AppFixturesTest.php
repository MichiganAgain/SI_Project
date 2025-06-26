<?php
/**
 * AppFixtures test cases.
 *
 * @license MIT
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AppFixturesTest.
 */
class AppFixturesTest extends TestCase
{
    /**
     * Tests if the load method calls flush on the object manager.
     */
    public function testLoadCallsFlush(): void
    {
        $manager = $this->createMock(ObjectManager::class);

        // Expect flush to be called exactly once
        $manager->expects($this->once())
            ->method('flush');

        $fixtures = new AppFixtures();
        $fixtures->load($manager);
    }
}
