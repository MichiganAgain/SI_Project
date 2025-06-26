<?php
/**
 * CategoryType test cases.
 *
 * @license MIT
 */

namespace App\Tests\Form\Type;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class CategoryTypeTest.
 *
 * Test cases for the CategoryType form.
 */
class CategoryTypeTest extends TypeTestCase
{
    /**
     * Test submitting valid data to the form.
     */
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

    /**
     * Test configureOptions method sets data_class correctly.
     */
    public function testConfigureOptions(): void
    {
        $formType = new CategoryType();

        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $formType->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertSame(Category::class, $options['data_class']);
    }

    /**
     * Test getBlockPrefix returns expected string.
     */
    public function testGetBlockPrefix(): void
    {
        $formType = new CategoryType();

        $this->assertSame('category', $formType->getBlockPrefix());
    }
}
