<?php
/**
 * Post controller tests.
 *
 * @license MIT
 */

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class PostControllerTest.
 */
class PostControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->logInAdminUser();
    }

    /**
     * Test index page for posts.
     */
    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/post');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    /**
     * Test creating a new post.
     */
    public function testCreatePost(): void
    {
        $category = new Category();
        $category->setTitle('Testowa kategoria');
        $category->setSlug('testowa-kategoria');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/post/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Utworz');

        $form = $crawler->selectButton('Zapisz')->form([
            'post[title]' => 'Nowy post',
            'post[category]' => $category->getId(),
            'post[content]' => 'Tresc nowego posta.',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/post');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $post = $this->entityManager->getRepository(Post::class)->findOneBy(['title' => 'Nowy post']);
        $this->assertNotNull($post);
    }

    /**
     * Test editing a post.
     */
    public function testEditPost(): void
    {
        $category = new Category();
        $category->setTitle('Kategoria do edycji');
        $category->setSlug('kategoria-do-edycji');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($category);

        $post = new Post();
        $post->setTitle('Stary post');
        $post->setSlug('stary-post');
        $post->setContent('Tresc starego posta');
        $post->setCategory($category);
        $post->setAuthor($this->getUser());
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/post/'.$post->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edytuj Post');

        $form = $crawler->selectButton('Edytuj')->form([
            'post[title]' => 'Zmieniony post',
            'post[category]' => $category->getId(),
            'post[content]' => 'Nowa tresc',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/post');

        $updatedPost = $this->entityManager->getRepository(Post::class)->find($post->getId());
        $this->assertSame('Zmieniony post', $updatedPost->getTitle());
    }

    /**
     * Test deleting a post.
     */
    public function testDeletePost(): void
    {
        $category = new Category();
        $category->setTitle('Kategoria do usuniecia');
        $category->setSlug('kategoria-do-usuniecia');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($category);

        $post = new Post();
        $post->setTitle('Post do usuniecia');
        $post->setSlug('post-do-usuniecia');
        $post->setContent('Tresc');
        $post->setCategory($category);
        $post->setAuthor($this->getUser());
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/post/'.$post->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Jestes pewien?');

        $form = $crawler->selectButton('Usun')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/post');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $deletedPost = $this->entityManager->getRepository(Post::class)->find($post->getId());
        $this->assertNull($deletedPost);
    }

    /**
     * Log in admin user.
     */
    private function logInAdminUser(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->getUser();

        if (!$user) {
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setUsername('AdminTestUser');
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($hasher->hashPassword($user, 'admin123'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->client->loginUser($user);
    }

    /**
     * Get test admin user.
     *
     * @return User|null returns user or null if not found
     */
    private function getUser(): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
    }
}
