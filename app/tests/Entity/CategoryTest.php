<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testSetAndGetId()
    {
        $category = new Category();

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($category, 123);

        $this->assertEquals(123, $category->getId());
    }

    public function testSetAndGetCreatedAt()
    {
        $category = new Category();
        $now = new \DateTimeImmutable();

        $category->setCreatedAt($now);
        $this->assertSame($now, $category->getCreatedAt());
    }

    public function testSetAndGetUpdatedAt()
    {
        $category = new Category();
        $now = new \DateTimeImmutable();

        $category->setUpdatedAt($now);
        $this->assertSame($now, $category->getUpdatedAt());
    }

    public function testSetAndGetTitle()
    {
        $category = new Category();
        $title = 'Test Category';

        $category->setTitle($title);
        $this->assertSame($title, $category->getTitle());
    }

    public function testSetAndGetSlug()
    {
        $category = new Category();
        $slug = 'test-category';

        $category->setSlug($slug);
        $this->assertSame($slug, $category->getSlug());
    }
}
