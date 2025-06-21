<?php

namespace App\Tests\Form\Type;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

class CategoryTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Test Category Title',
        ];

        $model = new Category();

        $form = $this->factory->create(CategoryType::class, $model);

        // Submit data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $category = $form->getData();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('Test Category Title', $category->getTitle());

        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('title', $children);
    }

    public function testConfigureOptions(): void
    {
        $formType = new CategoryType();

        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $formType->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertSame(Category::class, $options['data_class']);
    }

    public function testGetBlockPrefix(): void
    {
        $formType = new CategoryType();

        $this->assertSame('category', $formType->getBlockPrefix());
    }
}
