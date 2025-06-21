<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class HelloControllerTest.
 */
class HelloControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->logInUser();
    }

    public function testHelloWithName(): void
    {
        $this->client->request('GET', '/hello/Alice');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', 'Hello Alice!');
        $this->assertSelectorTextContains('title', 'Hello Alice!');
    }

    private function logInUser(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('test@example.com');
            $user->setUsername('TestNick'); // <- wymagane pole!
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($hasher->hashPassword($user, 'test123'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->client->loginUser($user);
    }

}
