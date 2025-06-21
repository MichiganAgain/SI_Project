<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\PostFixtures;
use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class PostFixturesTest extends TestCase
{
    public function testLoadDataCreatesPosts()
    {
        $manager = $this->createMock(ObjectManager::class);

        // Oczekujemy, że persist zostanie wywołane 100 razy z obiektami Post
        $manager->expects($this->exactly(100))
            ->method('persist')
            ->with($this->isInstanceOf(Post::class));

        $manager->expects($this->once())
            ->method('flush');

        $fixture = new PostFixtures();

        // Mock ReferenceRepository
        $referenceRepository = $this->createMock(ReferenceRepository::class);

        // Przygotujmy referencje dla kategorii i użytkowników
        $references = [];

        // Dodajemy po jednej referencji "categories_0" i "users_0"
        $category = new Category();
        $category->setTitle('Test Category');

        $user = new User();
        $user->setUsername('testuser');

        $references['categories_0'] = $category;
        $references['users_0'] = $user;

        // Metoda getReferences zwraca wszystkie dostępne referencje
        $referenceRepository->method('getReferences')->willReturn($references);

        // getReference zwraca odpowiedni obiekt po kluczu
        $referenceRepository->method('getReference')->willReturnCallback(function ($key) use ($references) {
            return $references[$key];
        });

        // Podstawiamy mock do chronionego pola referenceRepository
        $reflection = new \ReflectionClass($fixture);
        $parentClass = $reflection->getParentClass(); // AbstractBaseFixtures
        $property = $parentClass->getProperty('referenceRepository');
        $property->setAccessible(true);
        $property->setValue($fixture, $referenceRepository);

        // Wywołanie load() — wszystko powinno przejść
        $fixture->load($manager);
    }
}
