<?php
/**
 * User controller tests.
 *
 * @license MIT
 */

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    /**
     * Setup client, entity manager and login admin user.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->logInAdminUser();
    }

    /**
     * Test the user list page.
     */
    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/user');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Lista Uzytkownikow'); // dostosuj tłumaczenie jeśli trzeba
    }

    /**
     * Test creating a new user.
     */
    public function testCreateUser(): void
    {
        $crawler = $this->client->request('GET', '/user/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Utworz uzytkownka'); // dopasuj do formularza

        $form = $crawler->selectButton('Zapisz')->form([
            'user[email]' => 'newuser@example.com',
            'user[username]' => 'newuser',
            'user[password]' => 'password123',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/user');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($user);
        $this->assertSame('newuser', $user->getUsername());
    }

    /**
     * Test editing an existing user.
     */
    public function testEditUser(): void
    {
        $user = new User();
        $user->setEmail('edituser@example.com');
        $user->setUsername('edituser');
        $user->setPassword('dummy'); // dla testów wystarczy

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/user/'.$user->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'edytuj');

        $form = $crawler->selectButton('Edytuj')->form([
            'user[username]' => 'editeduser',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/user');

        $updatedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        $this->assertSame('editeduser', $updatedUser->getUsername());
    }

    /**
     * Test showing user details.
     */
    public function testShowUser(): void
    {
        $user = new User();
        $user->setEmail('showuser@example.com');
        $user->setUsername('showuser');
        $user->setPassword('dummy');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/user/'.$user->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Szczegoly Uzytkownika');

        $dtElements = $crawler->filter('dt');
        $this->assertGreaterThanOrEqual(2, $dtElements->count(), 'Za malo <dt> elementów');

        $secondDtText = $dtElements->eq(1)->text();
        $this->assertSame('email', $secondDtText, 'Drugi <dt> powinien zawierać "email"');
    }

    /**
     * Test deleting a user.
     */
    public function testDeleteUser(): void
    {
        $user = new User();
        $user->setEmail('deleteuser@example.com');
        $user->setUsername('deleteuser');
        $user->setPassword('dummy');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/user/'.$user->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'na pewno?');

        $form = $crawler->selectButton('Usun')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');

        $deletedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        $this->assertNull($deletedUser);
    }

    /**
     * Log in an admin user for tests.
     */
    private function logInAdminUser(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setUsername('AdminTestUser');
            $user->setRoles(['ROLE_ADMIN', 'MANAGE']);
            $user->setPassword($hasher->hashPassword($user, 'admin123'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->client->loginUser($user);
    }
}
