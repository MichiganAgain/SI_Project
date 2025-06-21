<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\CategoryFixtures;
use App\Entity\Category;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class CategoryFixturesTest extends TestCase
{
    public function testLoadDataCreatesCategories()
    {
        $manager = $this->createMock(ObjectManager::class);

        $manager->expects($this->exactly(20))
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));

        $manager->expects($this->once())
            ->method('flush');

        // Tworzymy instancję fixture
        $fixture = new CategoryFixtures();

        // Mock ReferenceRepository i przypisanie do chronionego pola referenceRepository
        $referenceRepository = $this->createMock(ReferenceRepository::class);
        $reflection = new \ReflectionClass($fixture);
        $property = $reflection->getParentClass()->getProperty('referenceRepository');
        $property->setAccessible(true);
        $property->setValue($fixture, $referenceRepository);

        // Teraz wywołujemy load(), wszystko powinno działać
        $fixture->load($manager);
    }
}
