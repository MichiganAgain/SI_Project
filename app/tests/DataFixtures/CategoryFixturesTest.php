<?php
/**
 * CategoryFixtures test cases.
 *
 * @license MIT
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\CategoryFixtures;
use App\Entity\Category;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryFixturesTest.
 */
class CategoryFixturesTest extends TestCase
{
    /**
     * Tests that load() creates 20 Category entities and flushes once.
     */
    public function testLoadDataCreatesCategories(): void
    {
        $manager = $this->createMock(ObjectManager::class);

        $manager->expects($this->exactly(20))
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));

        $manager->expects($this->once())
            ->method('flush');

        // Create fixture instance
        $fixture = new CategoryFixtures();

        // Mock ReferenceRepository and assign to protected referenceRepository property
        $referenceRepository = $this->createMock(ReferenceRepository::class);
        $reflection = new \ReflectionClass($fixture);
        $property = $reflection->getParentClass()->getProperty('referenceRepository');
        $property->setAccessible(true);
        $property->setValue($fixture, $referenceRepository);

        // Call load(), should work without errors
        $fixture->load($manager);
    }
}
