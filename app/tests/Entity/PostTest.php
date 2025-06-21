<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testIdIsInitiallyNull(): void
    {
        $post = new Post();
        $this->assertNull($post->getId());
    }

    public function testTitle(): void
    {
        $post = new Post();
        $title = 'Test Title';

        $post->setTitle($title);
        $this->assertSame($title, $post->getTitle());
    }

    public function testSlug(): void
    {
        $post = new Post();
        $slug = 'test-title';

        $post->setSlug($slug);
        $this->assertSame($slug, $post->getSlug());
    }

    public function testContent(): void
    {
        $post = new Post();
        $content = 'Test content';

        $post->setContent($content);
        $this->assertSame($content, $post->getContent());
    }

    public function testCreatedAt(): void
    {
        $post = new Post();
        $createdAt = new \DateTimeImmutable();

        $post->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $post->getCreatedAt());
    }

    public function testUpdatedAt(): void
    {
        $post = new Post();
        $updatedAt = new \DateTimeImmutable();

        $post->setUpdatedAt($updatedAt);
        $this->assertSame($updatedAt, $post->getUpdatedAt());
    }

    public function testCategory(): void
    {
        $post = new Post();
        $category = new Category();
        $category->setTitle('News');

        $post->setCategory($category);
        $this->assertSame($category, $post->getCategory());
    }

    public function testAuthor(): void
    {
        $post = new Post();
        $user = new User();
        $post->setAuthor($user);

        $this->assertSame($user, $post->getAuthor());
    }
}
