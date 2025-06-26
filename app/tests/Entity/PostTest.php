<?php
/**
 * PostTest test cases.
 *
 * @license MIT
 */

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class PostTest.
 *
 * Test cases for the Post entity.
 */
class PostTest extends TestCase
{
    /**
     * Test that the initial ID of Post is null.
     */
    public function testIdIsInitiallyNull(): void
    {
        $post = new Post();
        $this->assertNull($post->getId());
    }

    /**
     * Test setting and getting the title property.
     */
    public function testTitle(): void
    {
        $post = new Post();
        $title = 'Test Title';

        $post->setTitle($title);
        $this->assertSame($title, $post->getTitle());
    }

    /**
     * Test setting and getting the slug property.
     */
    public function testSlug(): void
    {
        $post = new Post();
        $slug = 'test-title';

        $post->setSlug($slug);
        $this->assertSame($slug, $post->getSlug());
    }

    /**
     * Test setting and getting the content property.
     */
    public function testContent(): void
    {
        $post = new Post();
        $content = 'Test content';

        $post->setContent($content);
        $this->assertSame($content, $post->getContent());
    }

    /**
     * Test setting and getting the createdAt property.
     */
    public function testCreatedAt(): void
    {
        $post = new Post();
        $createdAt = new \DateTimeImmutable();

        $post->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $post->getCreatedAt());
    }

    /**
     * Test setting and getting the updatedAt property.
     */
    public function testUpdatedAt(): void
    {
        $post = new Post();
        $updatedAt = new \DateTimeImmutable();

        $post->setUpdatedAt($updatedAt);
        $this->assertSame($updatedAt, $post->getUpdatedAt());
    }

    /**
     * Test setting and getting the category property.
     */
    public function testCategory(): void
    {
        $post = new Post();
        $category = new Category();
        $category->setTitle('News');

        $post->setCategory($category);
        $this->assertSame($category, $post->getCategory());
    }

    /**
     * Test setting and getting the author property.
     */
    public function testAuthor(): void
    {
        $post = new Post();
        $user = new User();
        $post->setAuthor($user);

        $this->assertSame($user, $post->getAuthor());
    }
}
