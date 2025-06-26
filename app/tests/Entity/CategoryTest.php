<?php
/**
 * CategoryTest test cases.
 *
 * @license MIT
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryTest.
 *
 * Tests for the Category entity.
 */
class CategoryTest extends TestCase
{
    /**
     * Test setting and getting the ID property.
     */
    public function testSetAndGetId(): void
    {
        $category = new Category();

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($category, 123);

        $this->assertEquals(123, $category->getId());
    }

    /**
     * Test setting and getting the createdAt property.
     */
    public function testSetAndGetCreatedAt(): void
    {
        $category = new Category();
        $now = new \DateTimeImmutable();

        $category->setCreatedAt($now);
        $this->assertSame($now, $category->getCreatedAt());
    }

    /**
     * Test setting and getting the updatedAt property.
     */
    public function testSetAndGetUpdatedAt(): void
    {
        $category = new Category();
        $now = new \DateTimeImmutable();

        $category->setUpdatedAt($now);
        $this->assertSame($now, $category->getUpdatedAt());
    }

    /**
     * Test setting and getting the title property.
     */
    public function testSetAndGetTitle(): void
    {
        $category = new Category();
        $title = 'Test Category';

        $category->setTitle($title);
        $this->assertSame($title, $category->getTitle());
    }

    /**
     * Test setting and getting the slug property.
     */
    public function testSetAndGetSlug(): void
    {
        $category = new Category();
        $slug = 'test-category';

        $category->setSlug($slug);
        $this->assertSame($slug, $category->getSlug());
    }
}
