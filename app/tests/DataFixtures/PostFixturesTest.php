<?php
/**
 * PostFixtures test cases.
 *
 * @license MIT
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\PostFixtures;
use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Post;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class PostFixturesTest.
 */
class PostFixturesTest extends TestCase
{
    private PostFixtures $postFixtures;

    /**
     * Setup PostFixtures instance.
     */
    protected function setUp(): void
    {
        $this->postFixtures = new PostFixtures();
    }

    /**
     * Test getDependencies returns expected fixture classes.
     */
    public function testGetDependencies(): void
    {
        $deps = $this->postFixtures->getDependencies();

        $this->assertIsArray($deps);
        $this->assertCount(2, $deps);
        $this->assertContains(CategoryFixtures::class, $deps);
        $this->assertContains(UserFixtures::class, $deps);
    }

    /**
     * Test loadData returns early if manager or faker is null.
     */
    public function testLoadDataReturnsEarlyIfManagerOrFakerIsNull(): void
    {
        $ref = new \ReflectionClass($this->postFixtures);

        // Set manager to null
        $propManager = $ref->getProperty('manager');
        $propManager->setAccessible(true);
        $propManager->setValue($this->postFixtures, null);

        // Set faker to null
        $propFaker = $ref->getProperty('faker');
        $propFaker->setAccessible(true);
        $propFaker->setValue($this->postFixtures, null);

        $method = $ref->getMethod('loadData');
        $method->setAccessible(true);

        // If manager and faker are null, method should just return without exception
        $method->invoke($this->postFixtures);

        $this->assertTrue(true); // test passes if no exception thrown
    }

    /**
     * Test loadData creates 100 Post entities and flushes once.
     */
    public function testLoadDataCreatesPosts(): void
    {
        // Mock ObjectManager
        $manager = $this->createMock(ObjectManager::class);

        // Expect persist() called exactly 100 times with Post instances
        $manager->expects($this->exactly(100))
            ->method('persist')
            ->with($this->isInstanceOf(Post::class));

        // Expect flush() called once
        $manager->expects($this->once())
            ->method('flush');

        // Faker with sensible fake data
        $faker = \Faker\Factory::create();

        $ref = new \ReflectionClass($this->postFixtures);

        // Set protected properties on original object (not used further)
        $propManager = $ref->getProperty('manager');
        $propManager->setAccessible(true);
        $propManager->setValue($this->postFixtures, $manager);

        $propFaker = $ref->getProperty('faker');
        $propFaker->setAccessible(true);
        $propFaker->setValue($this->postFixtures, $faker);

        // Create partial mock of PostFixtures overriding getRandomReference
        $postFixturesMock = $this->getMockBuilder(PostFixtures::class)
            ->onlyMethods(['getRandomReference'])
            ->getMock();

        // Set manager and faker in mock
        $propManager->setValue($postFixturesMock, $manager);
        $propFaker->setValue($postFixturesMock, $faker);

        // Initialize referenceRepository property in mock
        $referenceRepository = $this->createMock(\Doctrine\Common\DataFixtures\ReferenceRepository::class);
        $refReferenceRepo = new \ReflectionProperty(get_class($postFixturesMock), 'referenceRepository');
        $refReferenceRepo->setAccessible(true);
        $refReferenceRepo->setValue($postFixturesMock, $referenceRepository);

        // Mock Category and User entities
        $mockCategory = $this->createMock(\App\Entity\Category::class);
        $mockUser = $this->createMock(\App\Entity\User::class);

        // getRandomReference returns category or user depending on group name
        $postFixturesMock->method('getRandomReference')
            ->willReturnCallback(function (string $groupName) use ($mockCategory, $mockUser) {
                if ('categories' === $groupName) {
                    return $mockCategory;
                }
                if ('users' === $groupName) {
                    return $mockUser;
                }
                throw new \InvalidArgumentException(sprintf('Unexpected group name: %s', $groupName));
            });

        // Invoke loadData on mock
        $method = new \ReflectionMethod($postFixturesMock, 'loadData');
        $method->setAccessible(true);
        $method->invoke($postFixturesMock);

        $this->assertTrue(true);
    }
}
