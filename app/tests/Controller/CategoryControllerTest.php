<?php
/**
 * Category controller tests.
 *
 * @license MIT
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
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
     * Test index page.
     */
    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/category');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Lista kategorii');
    }

    /**
     * Test creating a category.
     */
    public function testCreateCategory(): void
    {
        $crawler = $this->client->request('GET', '/category/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Utworz Kategorie');

        $form = $crawler->selectButton('Zapisz')->form();
        $form['category[title]'] = 'Nowa Kategoria';

        $this->client->submit($form);
        $this->assertResponseRedirects('/category');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['title' => 'Nowa Kategoria']);
        $this->assertNotNull($category);
    }

    /**
     * Test editing a category.
     */
    public function testEditCategory(): void
    {
        $category = new Category();
        $category->setTitle('Edycja testowa');
        $category->setSlug('edycja-testowa');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/category/'.$category->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edytuj Kategorie');

        $form = $crawler->selectButton('Edytuj')->form();
        $form['category[title]'] = 'Zmieniona Kategoria';

        $this->client->submit($form);
        $this->assertResponseRedirects('/category');

        $updatedCategory = $this->entityManager->getRepository(Category::class)->find($category->getId());
        $this->assertSame('Zmieniona Kategoria', $updatedCategory->getTitle());
    }

    /**
     * Test deleting a category.
     */
    public function testDeleteCategory(): void
    {
        $category = new Category();
        $category->setTitle('Kategoria do usuniecia');
        $category->setSlug('kategoria-do-usuniecia');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/category/'.$category->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Jestes pewien?');

        $form = $crawler->selectButton('Usun')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/category');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $deletedCategory = $this->entityManager->getRepository(Category::class)->find($category->getId());
        $this->assertNull($deletedCategory);
    }

    /**
     * Test showing a category.
     */
    public function testShowCategory(): void
    {
        $category = new Category();
        $category->setTitle('officiis');
        $category->setSlug('officiis');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/category/'.$category->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Szczegoly kategorii');
        $this->assertSelectorTextContains('body', 'officiis');
    }

    /**
     * Log in test admin user.
     */
    private function logInAdminUser(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);

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
}
