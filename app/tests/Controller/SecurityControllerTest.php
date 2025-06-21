<?php

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

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testLoginPageIsSuccessful(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h1', 'Zaloguj Sie'); // or "Zaloguj sie" if that's your translation
    }

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

    public function testLoginWithValidCredentials(): void
    {
        $user = $this->createUser('loginuser@example.com', 'testpass');

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Zaloguj Sie')->form([
            'email' => 'loginuser@example.com',
            'password' => 'testpass',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects(); // default route or homepage

        // Optional: follow redirect and assert user is logged in
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.navbar-brand', 'Witaj TestUser!');
    }

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
