<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Service\CategoryService;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\QueryBuilder;

class CategoryServiceTest extends TestCase
{
    public function testSaveSetsTimestamps()
    {
        $category = new Category();

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('save')->with($category);

        $service = new CategoryService(
            $categoryRepository,
            $this->createMock(PostRepository::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->save($category);

        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getUpdatedAt());
    }

    public function testCanBeDeletedWhenNoPosts()
    {
        $category = new Category();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByCategory')->willReturn(0);

        $service = new CategoryService(
            $this->createMock(CategoryRepository::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertTrue($service->canBeDeleted($category));
    }

    public function testCanBeDeletedWhenException()
    {
        $category = new Category();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByCategory')->willThrowException(new NonUniqueResultException());

        $service = new CategoryService(
            $this->createMock(CategoryRepository::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertFalse($service->canBeDeleted($category));
    }

    public function testGetPaginatedList(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $paginationMock = $this->createMock(PaginationInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects($this->once())
            ->method('paginate')
            ->with($queryBuilder, 1, PostRepository::PAGINATOR_ITEMS_PER_PAGE)
            ->willReturn($paginationMock);

        $postRepository = $this->createMock(PostRepository::class);
        $security = $this->createMock(Security::class);

        $service = new CategoryService(
            $categoryRepository,
            $postRepository,
            $paginator,
            $security
        );

        $this->assertSame($paginationMock, $service->getPaginatedList(1));
    }

    public function testDeleteWhenCategoryCanBeDeleted(): void
    {
        $category = new Category();

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('delete')
            ->with($category);

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByCategory')->willReturn(0);

        $service = new CategoryService(
            $categoryRepository,
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertTrue($service->delete($category));
    }

    public function testDeleteWhenCategoryCannotBeDeleted(): void
    {
        $category = new Category();

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->never())->method('delete');

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByCategory')->willReturn(5);

        $service = new CategoryService(
            $categoryRepository,
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertFalse($service->delete($category));
    }
}
