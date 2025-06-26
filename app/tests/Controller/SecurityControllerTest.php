<?php
/**
 * Category controller tests.
 *
 * @license MIT
 */

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    /**
     * Setup client and entity manager.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Test that login page loads successfully.
     */
    public function testLoginPageIsSuccessful(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h1', 'Zaloguj Sie');
    }

    /**
     * Test login attempt with invalid credentials.
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Zaloguj Sie')->form([
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }

    /**
     * Test login with valid credentials.
     */
    public function testLoginWithValidCredentials(): void
    {
        $user = $this->createUser('loginuser@example.com', 'testpass');

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Zaloguj Sie')->form([
            'email' => 'loginuser@example.com',
            'password' => 'testpass',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.navbar-brand', 'Witaj TestUser!');
    }

    /**
     * Helper method to create a user in the database.
     *
     * @param string $email    user email address
     * @param string $password user password
     *
     * @return User created user entity
     */
    private function createUser(string $email, string $password): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail($email);
        $user->setUsername('TestUser');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
