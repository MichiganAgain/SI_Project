<?php
namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\UserType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([new UserType()], []),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'secret123',
        ];

        $model = new User();

        $form = $this->factory->create(UserType::class, $model);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        /** @var User $submitted */
        $submitted = $form->getData();

        $this->assertSame('testuser', $submitted->getUsername());
        $this->assertSame('test@example.com', $submitted->getEmail());
        $this->assertSame('secret123', $submitted->getPassword());

        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('username', $children);
        $this->assertArrayHasKey('email', $children);
        $this->assertArrayHasKey('password', $children);
    }

    public function testGetBlockPrefix(): void
    {
        $type = new UserType();
        $this->assertSame('user', $type->getBlockPrefix());
    }
}
